<?php

namespace Models;

use DateTime;
use Exception;
use DateTimeZone;
use App\Models\Comment;
use InvalidArgumentException;
use App\Core\DatabaseInterface;

class CommentsRepository
{

    /**
     * The database interface.
     *
     * The database interface for interacting with the database.
     *
     * @var DatabaseInterface $dbi
     */
    private DatabaseInterface $dbi;


    /**
     * Constructeur qui injecte la dépendance vers la couche d'accès aux données.
     *
     * @param DatabaseInterface $dbi Interface pour interagir avec la base de données.
     */
    public function __construct(DatabaseInterface $dbi)
    {
        $this->dbi = $dbi;

    }//end __construct()


    /**
     * Insère un nouveau commentaire dans la base de données.
     *
     * @param Comment $comment L'objet commentaire à insérer.
     *
     * @return Comment L'objet commentaire avec l'ID nouvellement assigné.
     *
     * @throws Exception Si l'insertion échoue.
     */
    public function createComment(Comment $comment): Comment
    {
        $sql = "INSERT INTO comment (content, created_at, post_id, author, status) VALUES (:content, :created_at, :post_id, :author, :status)";

        $stmt = $this->dbi->prepare($sql);

        $stmt->bindValue(':content', $comment->getContent());
        // Récupérer l'heure actuelle avec le fuseau horaire correct.
        $createdAt = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $stmt->bindValue(':created_at', $createdAt->format('Y-m-d H:i:s'));
        $stmt->bindValue(':post_id', $comment->getPostId());
        $stmt->bindValue(':author', $comment->getAuthor());
        $stmt->bindValue(':status', $comment->getStatus());

        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Échec de l'insertion du commentaire dans la base de données.");
        }

        $comment->setId((int) $this->dbi->lastInsertId());
        $comment->setCreatedAt($createdAt);

        return $comment;

    }//end createComment()


    /**
     * Supprime un commentaire de la base de données.
     *
     * @param int $commentId L'ID du commentaire à supprimer.
     *
     * @return bool
     */
    public function deleteComment(int $commentId): bool
    {
        $sql = 'DELETE FROM comment WHERE comment_id = :comment_id';

        $stmt = $this->dbi->prepare($sql);

        $stmt->bindValue(':comment_id', $commentId);

        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception('Échec de la suppression du commentaire.');
        }

        return true;

    }//end deleteComment()


    /**
     * Récupère tous les commentaires.
     *
     * @return Comment[] Un tableau d'objets Comment.
     */
    public function findAll(): array
    {
        // 'query' retourne maintenant un Iterator.
        $results = $this->dbi->query("SELECT * FROM comment");

        return array_map([$this, 'createCommentFromResult'], iterator_to_array($results));

    }//end findAll()


    /**
     * Trouve un commentaire par son ID.
     *
     * @param int $commentId L'ID du commentaire à trouver.
     *
     * @return Comment|null Le commentaire trouvé ou null s'il n'existe pas.
     */
    public function findById(int $commentId): ?Comment
    {
        $sql    = 'SELECT * FROM comment WHERE comment_id = :comment_id';
        $params = [':comment_id' => $commentId];

        $stmt = $this->dbi->prepare($sql);
        $this->dbi->execute($stmt, $params);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $data !== false ? $this->createCommentFromResult($data) : null;

    }//end findById()


    /**
     * Récupère les commentaires selon leur statut.
     *
     * @param string $status Le statut de validation (pending, validated, rejected).
     *
     * @return array Un tableau de commentaires.
     */
    public function findCommentsByStatus(string $status): array
    {
        $sql    = 'SELECT c.*, CONCAT(u.first_name, " ", u.last_name) AS author_username, p.title AS post_title
            FROM comment c
            JOIN user u ON c.author = u.user_id
            JOIN post p ON c.post_id = p.post_id
            WHERE c.status = :status';
        $params = [':status' => $status];

        $stmt = $this->dbi->prepare($sql);
        $this->dbi->execute($stmt, $params);

        $comments = [];
        while (($row = $stmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $comment = $this->createCommentFromResult($row);

            // Regrouper les données dans un tableau associatif.
            $comments[] = [
                'comment' => $comment,
                'authorUsername' => $row['author_username'],
                'postTitle' => $row['post_title'],
            ];
        }

        return $comments;

    }//end findCommentsByStatus()


    /**
     * Récupère les commentaires validés pour un post donné.
     *
     * @param int $postId L'ID du post.
     *
     * @return Comment[] Un tableau d'objets Comment.
     */
    public function findValidatedCommentsByPostId(int $postId): array
    {
        $sql    = 'SELECT c.*, CONCAT(u.first_name, " ", u.last_name) AS author_username
            FROM comment c
            JOIN user u ON c.author = u.user_id
            WHERE c.post_id = :post_id AND c.status = :status';
        $params = [
            ':post_id' => $postId,
            ':status' => 'validated',
        ];

        $stmt = $this->dbi->prepare($sql);
        $this->dbi->execute($stmt, $params);

        $comments = [];
        while (($row = $stmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $comment = $this->createCommentFromResult($row);

            // Inclure le nom d'utilisateur de l'auteur.
            $comments[] = [
                'comment' => $comment,
                'authorUsername' => $row['author_username'],
            ];
        }

        return $comments;

    }//end findValidatedCommentsByPostId()


    /**
     * Met à jour le statut de validation d'un commentaire.
     *
     * @param int    $commentId L'identifiant du commentaire à mettre
     *                          à jour.
     * @param string $status    Le nouveau statut de validation.
     *
     * @return bool Retourne true si la mise à jour a réussi, sinon false.
     *
     * @throws Exception Si la mise à jour échoue pour une raison quelconque.
     */
    public function updateCommentStatus(int $commentId, string $status): bool
    {
        $sql    = "UPDATE comment SET status = :status WHERE comment_id = :comment_id";
        $stmt   = $this->dbi->prepare($sql);
        $params = [
            ':status' => $status,
            ':comment_id'   => $commentId,
        ];

        if ($this->dbi->execute($stmt, $params) === false) {
            throw new Exception("Échec de la mise à jour du statut du commentaire dans la base de données.");
        }

        return true;

    }//end updateCommentStatus()


    /**
     * Crée une instance de Comment à partir d'un tableau de données.
     * Cette méthode vérifie que toutes les données requises sont présentes
     * et utilise des paramètres nommés pour plus de clarté lors de la création de l'objet Comment.
     *
     * @param array $row Les données du commentaire extraites de la base de données.
     *
     * @return Comment|null L'instance de Comment créée, ou null si les données essentielles manquent.
     *
     * @throws InvalidArgumentException Si des données obligatoires sont manquantes.
     */
    private function createCommentFromResult(array $row): ?Comment
    {
        $this->validateRow($row);

        return $this->buildCommentFromRow($row);

    }//end createCommentFromResult()


    /**
     * Valide la ligne de données pour s'assurer que tous les champs obligatoires sont présents.
     *
     * @param array $row La ligne de données à valider.
     *
     * @return void
     *
     * @throws InvalidArgumentException Si des champs obligatoires sont manquants.
     */
    private function validateRow(array $row): void
    {
        $requiredFields = [
            'comment_id',
            'content',
            'created_at',
            'post_id',
            'author',
            'status',
        ];

        foreach ($requiredFields as $field) {
            if (array_key_exists($field, $row) === false || $row[$field] === '') {
                throw new InvalidArgumentException("Le champ '$field' est requis.");
            }
        }

    }//end validateRow()


    /**
     * Construit une instance de Comment à partir de la ligne de données.
     *
     * @param array $row La ligne de données contenant les informations du commentaire.
     *
     * @return Comment L'instance de Comment créée.
     */
    private function buildCommentFromRow(array $row): Comment
    {
        return new Comment(
            [
                'commentId' => (int) $row['comment_id'],
                'content' => $row['content'],
                'createdAt' => $row['created_at'],
                'postId' => (int) $row['post_id'],
                'author' => (int) $row['author'],
                'status' => $row['status'],
            ]
        );

    }//end buildCommentFromRow()


}//end class
