<?php

namespace Models;

use DateTime;
use Exception;
use App\Models\Comment;
use InvalidArgumentException;
use App\Core\Database\DatabaseInterface;

class CommentsRepository
{
    /**
     * @var DatabaseInterface $dbi
     *
     * The database interface for interacting with the database.
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
    }


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
        $sql = "INSERT INTO comments (content, created_at, is_validated, post_id, author) VALUES (:content, :created_at, :is_validated, :post_id, :author)";

        $stmt = $this->dbi->prepare($sql);

        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':created_at', $comment->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':is_validated', $comment->isValidated(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':post_id', $comment->getPostId());
        $stmt->bindValue(':author', $comment->getAuthor());
        $stmt->bindValue(':comment_id', $comment->getCommentId());

        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to insert the comment into the database.");
        }

        $comment->setCommentId((int) $this->dbi->lastInsertId());

        return $comment;
    }


    /**
     * Récupère tous les articles.
     *
     * @return Post[] Un tableau d'objets Post.
     */
    public function findAll(): array
    {
        // 'query' retourne maintenant un Iterator.
        $results = $this->dbi->query("SELECT * FROM comment");

        // Initialiser un tableau pour stocker les objets Post.
        $comments = [];

        // Parcourir chaque ligne retournée par la requête.
        foreach ($results as $row) {
            $comments[] = $this->createCommentFromResult($row);
        }

        return $comments;
    }


    /**
     * Met à jour le statut de validation d'un commentaire.
     *
     * @param int  $commentId  L'identifiant du commentaire à mettre à jour.
     * @param bool $isValidated Le nouveau statut de validation.
     * @return bool Retourne true si la mise à jour a réussi, sinon false.
     * @throws Exception Si la mise à jour échoue pour une raison quelconque.
     */
    public function updateCommentStatus(int $commentId, bool $isValidated): bool
    {
        $sql = "UPDATE comments SET is_validated = :is_validated WHERE comment_id = :comment_id";
        $stmt = $this->dbi->prepare($sql);
        $params = [
                   ':is_validated' => $isValidated,
                   ':comment_id' => $commentId
                  ];
        if (!$this->dbi->execute($stmt, $params) === false) {
            throw new Exception("Failed to update the comment status in the database.");
        }

        return true;
    }


    /**
     * Crée une instance de Comment à partir d'un tableau de données.
     * Cette méthode vérifie que toutes les données requises sont présentes
     * et utilise des paramètres nommés pour plus de clarté lors de la création de l'objet Comment.
     *
     * @param array $row Les données du commentaire extraites de la base de données.
     * @return Comment L'instance de Comment créée, ou null si les données essentielles manquent.
     * @throws InvalidArgumentException Si des données obligatoires sont manquantes.
     */
    private function createCommentFromResult(array $row): ?Comment
    {
        if (
            empty($row['comment_id'])
            || empty($row['content'])
            || empty($row['created_at'])
            || !isset($row['is_validated'])
            || empty($row['post_id'])
            || empty($row['author'])
        ) {
            throw new InvalidArgumentException("All fields are required.");
        }

        return new Comment(
            commentId: (int) $row['comment_id'],
            content: $row['content'],
            createdAt: new DateTime($row['created_at']),
            isValidated: (bool) $row['is_validated'],
            postId: (int) $row['post_id'],
            author: (int) $row['author']
        );
    }
}
