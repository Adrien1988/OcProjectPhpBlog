<?php

namespace Models;

use App\Models\Comment;
use App\Core\Database\DatabaseInterface;
use DateTime;

class CommentsRepository
{
    private DatabaseInterface $db;

    /**
     * Constructeur qui injecte la dépendance vers la couche d'accès aux données.
     *
     * @param DatabaseInterface $db Interface pour interagir avec la base de données.
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    public function createComment(Comment $comment): Comment 
    {
        $sql = "INSERT INTO comments (content, created_at, is_validated, post_id, author) VALUES (:content, :created_at, :is_validated, :post_id, :author)";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':content', $comment->getContent());
        $stmt->bindValue(':created_at', $comment->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':is_validated', $comment->getIsValidated(), \PDO::PARAM_BOOL);
        $stmt->bindValue(':post_id', $comment->getPostId());
        $stmt->bindValue(':author', $comment->getAuthor());
        $stmt->bindValue(':comment_id', $comment->getCommentId());

        if (!$this->db->execute($stmt, [])) {
            throw new \Exception("Failed to insert the comment into the database.");
        }

        $comment->setCommentId((int) $this->db->lastInsertId());

        return $comment;
    }







    /**
     * Crée une instance de Comment à partir d'un tableau de données.
     * Cette méthode vérifie que toutes les données requises sont présentes
     * et utilise des paramètres nommés pour plus de clarté lors de la création de l'objet Comment.
     *
     * @param array $row Les données du commentaire extraites de la base de données.
     * @return Comment L'instance de Comment créée, ou null si les données essentielles manquent.
     * @throws \InvalidArgumentException Si des données obligatoires sont manquantes.
     */
    private function createCommentFromResult(array $row): ?Comment
    {
        if (
            empty($row['comment_id']) || empty($row['content']) || empty($row['created_at']) || !isset($row['is_validated']) || empty($row['post_id']) || empty($row['author'])
        ) {
            throw new \InvalidArgumentException("All fields are required.");
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