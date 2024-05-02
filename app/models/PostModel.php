<?php

namespace App\Models;

use App\Core\Database\DatabaseInterface;

class PostModel
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère tous les articles, triés par date de création.
     * 
     * @return array Les articles récupérés sous forme de tableau associatif.
     */
    public function getAllPosts(): array
    {
        return $this->db->query("SELECT * FROM post ORDER BY created_at DESC", []);
    }

    /**
     * Récupère un article par son ID.
     * 
     * @param int $postId L'ID de l'article à récupérer.
     * @return array|false Les détails de l'article ou false si rien n'est trouvé.
     */
    public function getPostById(int $postId): array|false
    {
       $result = $this->db->prepare("SELECT * FROM post WHERE id = :id", ['id' => $postId]);
       return $result ? $result[0]: false;
    }

    /**
     * Ajoute un nouvel article dans la base de données.
     * 
     * @param string $title Le titre de l'article.
     * @param string $content Le contenu de l'article.
     * @param int $userId L'ID de l'utilisateur qui crée l'article.
     * @return int L'ID de l'article nouvellement créé.
     */
    public function addPost(string $title, string $content, int $userId): int {
        $this->db->prepare(
            "INSERT INTO posts (title, content, user_id) VALUES (?, ?, ?)",
            [$title, $content, $userId]
        );
        return (int) $this->db->lastInsertId();
    }
}
