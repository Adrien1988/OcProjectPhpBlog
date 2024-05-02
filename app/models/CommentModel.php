<?php

namespace App\Models;

use App\Core\Database\DatabaseInterface;

class CommentModel
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les commentaires associés à un ID de publication spécifique.
     *
     * @param int $postId L'ID du post pour lequel récupérer les commentaires.
     * @return array Renvoie un tableau de tous les commentaires pour le post spécifié.
     */
    public function getCommentsByPostId(int $postId):array
    {
        $result = $this->db->prepare("SELECT * FROM comment WHERE post_id = ? ORDER BY created_at DESC", [$postId]);
        return $result;
    }

    /**
     * Ajoute un nouveau commentaire à un article.
     *
     * @param string $content Le contenu du commentaire.
     * @param int $postId L'ID du post auquel le commentaire est associé.
     * @param int $userId L'ID de l'utilisateur qui a écrit le commentaire.
     * @return int L'ID du commentaire nouvellement inséré.
     */
    public function addComment(string $content, int $postId, int $userId): int
    {
        $this->db->prepare("INSERT INTO comment (content, post_id, user_id) VALUES (?, ?, ?)", [$content, $postId, $userId]);
        return (int) $this->db->lastInsertId();
    }
}