<?php

namespace Models;

use App\Models\Post;
use App\Core\Database\DatabaseInterface;
use DateTime;

/**
 * Gère les opérations de la base de données pour les entités Post.
 */
class PostsRepository
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
     * Récupère tous les articles.
     *
     * @return Post[] Un tableau d'objets Post.
     */
    public function findAll(): array
    {
        // 'query' retourne maintenant un Iterator.
        $results = $this->dbi->query("SELECT * FROM post");

        // Initialiser un tableau pour stocker les objets Post.
        $posts = [];

        // Parcourir chaque ligne retournée par la requête.
        foreach ($results as $row) {
            $posts[] = $this->createPostFromResult($row);
        }

        return $posts;

    }


    /**
     * Récupère un article par son identifiant.
     *
     * @param int $postId L'identifiant de l'article à récupérer.
     * @return Post|null Retourne l'objet Post si trouvé, sinon null.
     */
    public function findById(int $postId): ?Post
    {
        // Prépare et exécute la requête pour obtenir un seul enregistrement basé sur l'ID.
        $result = $this->dbi->prepare("SELECT * FROM post WHERE post_id = :id", ['id' => $postId]);

        // Vérifie si le résultat contient au moins un enregistrement.
        if (!empty($result)) {
            // Utilise createPostFromResult pour transformer le premier enregistrement trouvé en objet Post.
            return $this->createPostFromResult($result[0]);
        }

        // Retourne null si aucun enregistrement n'est trouvé.
        return null;

    }


    /**
     * Insère un nouvel article dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour insérer un nouvel article dans la base de données,
     * lie les valeurs de l'article à la requête pour éviter les injections SQL, et exécute la requête.
     * Après l'insertion, elle récupère l'ID de la ligne insérée et le définit sur l'objet Post.
     *
     * @param Post $post L'objet Post à insérer dans la base de données.
     * @return Post Retourne l'objet Post avec l'identifiant attribué après l'insertion.
     * @throws \Exception Si l'insertion échoue pour une raison quelconque.
     */
    public function createPost(Post $post): Post
    {
        // La requête SQL pour insérer un nouvel article.
        $sql = "INSERT INTO post (title, chapo, content, author, created_at, updated_at) 
                VALUES (:title, :chapo, :content, :author, :created_at, :updated_at)";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison des valeurs à la requête préparée.
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':chapo', $post->getChapo());
        $stmt->bindValue(':content', $post->getContent());
        $stmt->bindValue(':author', $post->getAuthor());
        $stmt->bindValue(':created_at', $post->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null);

        // Exécution de la requête
        if (!$this->dbi->execute($stmt, [])) {
            throw new \Exception("Failed to insert the post into the database.");
        }

        // Récupération et définition de l'ID de la dernière ligne insérée.
        $post->setPostId((int) $this->dbi->lastInsertId());

        return $post;

    }


    /**
     * Met à jour un article existant dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour mettre à jour un article spécifique,
     * lie les valeurs de l'article à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle met à jour toutes les propriétés modifiables de l'article.
     *
     * @param Post $post L'objet Post à mettre à jour dans la base de données.
     * @return bool Retourne true si la mise à jour a réussi, sinon false.
     * @throws \Exception Si la mise à jour échoue pour une raison quelconque.
     */
    public function updatePost(Post $post): bool
    {
        // La requête SQL pour mettre à jour un article existant.
        $sql = "UPDATE posts SET title = :title, chapo = :chapo, content = :content, 
                author = :author, created_at = :created_at, updated_at = :updated_at 
                WHERE post_id = :post_id";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison des valeurs à la requête préparée.
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':chapo', $post->getChapo());
        $stmt->bindValue(':content', $post->getContent());
        $stmt->bindValue(':author', $post->getAuthor());
        $stmt->bindValue(':created_at', $post->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $post->getUpdatedAt() ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':post_id', $post->getPostId());

        // Exécution de la requête.
        if (!$this->dbi->execute($stmt, [])) {
            throw new \Exception("Failed to update the post in the database.");
        }

        return true;

    }


    /**
     * Supprime un article existant dans la base de données basé sur son identifiant.
     *
     * Cette méthode prépare une requête SQL pour supprimer un article,
     * lie l'identifiant de l'article à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle est sécurisée et ne permet que la suppression par identifiant.
     *
     * @param int $postId L'identifiant de l'article à supprimer.
     * @return bool Retourne true si la suppression a réussi, sinon false.
     * @throws \Exception Si la suppression échoue pour une raison quelconque.
     */
    public function deletePost(int $postId): bool
    {
        // La requête SQL pour supprimer un article.
        $sql = "DELETE FROM posts WHERE post_id = :post_id";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison de l'identifiant à la requête préparée.
        $stmt->bindValue(':post_id', $postId);

        // Exécution de la requête.
        if (!$this->dbi->execute($stmt, [])) {
            throw new \Exception("Failed to delete the post from the database.");
        }

        return true;

    }


    /**
     * Crée un objet Post à partir d'une ligne de résultat de la base de données.
     * Cette méthode extrait les informations nécessaires de la ligne de données et initialise
     * un objet Post avec ces données. Elle vérifie que toutes les données obligatoires sont
     * présentes et lève une exception si des données essentielles manquent.
     *
     * @param array $row Une ligne de résultat sous forme de tableau associatif contenant les données de l'article.
     * @return Post|null L'objet Post initialisé à partir des données de la ligne, ou null si des données essentielles manquent.
     * @throws InvalidArgumentException Si des champs obligatoires sont manquants.
     */
    private function createPostFromResult(array $row): ?Post
    {
        // Vérification de la présence de tous les champs requis dans la ligne de données.
        if (empty($row['post_id'])
            || empty($row['title'])
            || empty($row['chapo'])
            || empty($row['content'])
            || empty($row['author'])
            || empty($row['created_at'])
        ) {
            throw new \InvalidArgumentException("All fields except 'updated_at' are required.");
        }

        // Création de l'instance de Post avec les données récupérées, en utilisant des paramètres nommés pour plus de clarté.
        return new Post(
            postId: (int) $row['post_id'],
            title: $row['title'],
            chapo: $row['chapo'],
            content: $row['content'],
            author: (int) $row['author'],
            createdAt: new DateTime($row['created_at']),
            updatedAt: isset($row['updated_at']) ? new DateTime($row['updated_at']) : null
        );

    }


}
