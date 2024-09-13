<?php

namespace Models;

use DateTime;
use Exception;
use App\Models\Post;
use InvalidArgumentException;
use App\Core\DatabaseInterface;

/**
 * Gère les opérations de la base de données pour les entités Post.
 */
class PostsRepository
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
    } //end __construct()


    /**
     * Récupère tous les articles.
     *
     * @return Post[] Un tableau d'objets Post.
     */
    public function findAll(): array
    {
        // 'query' retourne maintenant un Iterator.
        $results = $this->dbi->query("SELECT post_id, title, chapo, content, author, created_at, updated_at FROM post");

        // Initialiser un tableau pour stocker les objets Post.
        $posts = [];

        // Parcourir chaque ligne retournée par la requête.
        foreach ($results as $row) {
            var_dump($row);
            $posts[] = $this->createPostFromResult($row);
        }

        return $posts;
    } //end findAll()


    /**
     * Récupère un article par son identifiant.
     *
     * @param int $postId L'identifiant de l'article à récupérer.
     *
     * @return Post|null Retourne l'objet Post si trouvé, sinon null.
     */
    public function findById(int $postId): ?Post
    {
        // Prépare la requête SQL.
        $stmt = $this->dbi->prepare("SELECT * FROM post WHERE post_id = :post_id");

        // Exécute la requête avec l'ID du post
        if ($this->dbi->execute($stmt, [':post_id' => $postId]) === false) {
            throw new \Exception('Erreur lors de l\'exécution de la requête.');
        }

        // Récupère le premier résultat
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Vérifie si le résultat contient au moins un enregistrement.
        if ($result !== false && $result !== null) {
            return $this->createPostFromResult($result);
        }

        // Retourne null si aucun enregistrement n'est trouvé.
        return null;
    } //end findById()


    /**
     * Insère un nouvel article dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour insérer un nouvel article dans la base de données,
     * lie les valeurs de l'article à la requête pour éviter les injections SQL, et exécute la requête.
     * Après l'insertion, elle récupère l'ID de la ligne insérée et le définit sur l'objet Post.
     *
     * @param Post $post L'objet Post à insérer dans la base de données.
     *
     * @return Post Retourne l'objet Post avec l'identifiant attribué après l'insertion.
     *
     * @throws Exception Si l'insertion échoue pour une raison quelconque.
     */
    public function createPost(Post $post): Post
    {
        // La requête SQL pour insérer un nouvel article.
        $sql = "INSERT INTO `post` (`title`, `chapo`, `content`, `author`, `created_at`, `updated_at`) 
        VALUES (:title, :chapo, :content, :author, :created_at, :updated_at)";


        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison des valeurs à la requête préparée.
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':chapo', $post->getChapo());
        $stmt->bindValue(':content', $post->getContent());
        $stmt->bindValue(':author', $post->getAuthor());
        $stmt->bindValue(':created_at', $post->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $post->getUpdatedAt() !== null ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null);


        // Exécution de la requête.
        if ($this->dbi->execute($stmt) === false) {
            throw new Exception("Failed to insert the post into the database.");
        }

        // Récupération et définition de l'ID de la dernière ligne insérée.
        $post->setPostId((int) $this->dbi->lastInsertId());

        return $post;
    } //end createPost()


    /**
     * Met à jour un article existant dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour mettre à jour un article spécifique,
     * lie les valeurs de l'article à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle met à jour toutes les propriétés modifiables de l'article.
     *
     * @param Post $post L'objet Post à mettre à jour dans la base de données.
     *
     * @return bool Retourne true si la mise à jour a réussi, sinon false.
     *
     * @throws Exception Si la mise à jour échoue pour une raison quelconque.
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
        $stmt->bindValue(':updated_at', $post->getUpdatedAt() !== null ? $post->getUpdatedAt()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':post_id', $post->getPostId());

        // Exécution de la requête.
        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to update the post in the database.");
        }

        return true;
    } //end updatePost()


    /**
     * Supprime un article existant dans la base de données basé sur son identifiant.
     *
     * Cette méthode prépare une requête SQL pour supprimer un article,
     * lie l'identifiant de l'article à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle est sécurisée et ne permet que la suppression par identifiant.
     *
     * @param int $postId L'identifiant de l'article à supprimer.
     *
     * @return bool Retourne true si la suppression a réussi, sinon false.
     *
     * @throws Exception Si la suppression échoue pour une raison quelconque.
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
        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to delete the post from the database.");
        }

        return true;
    } //end deletePost()


    /**
     * Récupère les derniers articles par date de création.
     *
     * @param int $limit Le nombre maximum d'articles à retourner.
     *
     * @return Post[] Un tableau d'objets Post.
     */
    public function findLatest(int $limit = 5): array
    {
        $sql     = "SELECT * FROM post ORDER BY created_at DESC LIMIT " . intval($limit);
        $results = $this->dbi->query($sql);

        $posts = [];
        foreach ($results as $row) {
            $posts[] = $this->createPostFromResult($row);
        }

        return $posts;
    } //end findLatest()


    /**
     * Crée un objet Post à partir d'une ligne de résultat de la base de données.
     * Cette méthode extrait les informations nécessaires de la ligne de données et initialise
     * un objet Post avec ces données. Elle vérifie que toutes les données obligatoires sont
     * présentes et lève une exception si des données essentielles manquent.
     *
     * @param array $row Une ligne de résultat sous forme de tableau associatif contenant les données de l'article.
     *
     * @return Post|null L'objet Post initialisé à partir des données de la ligne, ou null si des données essentielles manquent.
     *
     * @throws InvalidArgumentException Si des champs obligatoires sont manquants.
     */
    private function createPostFromResult(array $row): ?Post
    {
        $this->validateRow($row);

        return $this->buildPostFromRow($row);
    } //end createPostFromResult()


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
            'title',
            'chapo',
            'content',
            'author',
            'created_at',
        ];

        foreach ($requiredFields as $field) {
            if (array_key_exists($field, $row) === false || $row[$field] === '') {
                throw new InvalidArgumentException("Tous les champs sauf 'updated_at' sont requis.");
            }
        }
    } //end validateRow()


    /**
     * Construit une instance de Post à partir de la ligne de données.
     *
     * @param array $row La ligne de données contenant les informations de l'article.
     *
     * @return Post L'instance de Post créée.
     */
    private function buildPostFromRow(array $row): Post
    {
        return new Post(
            postId: (int) $row['post_id'],
            title: $row['title'],
            chapo: $row['chapo'],
            content: $row['content'],
            author: (int) $row['author'],
            createdAt: new DateTime($row['created_at']),
            updatedAt: isset($row['updated_at']) && $row['updated_at']  !== null ? new DateTime($row['updated_at']) : null
        );
    } //end buildPostFromRow()


}//end class
