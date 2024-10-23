<?php

namespace Models;

use DateTime;
use App\Models\Post;
use InvalidArgumentException;
use App\Core\DatabaseInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * Le validateur Symfony.
     *
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;


    /**
     * Constructeur qui injecte la dépendance vers la couche d'accès aux données.
     *
     * @param DatabaseInterface  $dbi       Interface pour interagir avec la base de
     *                                      données.
     * @param ValidatorInterface $validator Le validateur Symfony.
     */
    public function __construct(DatabaseInterface $dbi, ValidatorInterface $validator)
    {
        $this->dbi       = $dbi;
        $this->validator = $validator;

    }//end __construct()


    /**
     * Récupère tous les articles.
     *
     * @return Post[] Un tableau d'objets Post.
     */
    public function findAll(): array
    {
        // 'query' retourne maintenant un Iterator.
        $results = $this->dbi->query("SELECT * FROM post ORDER BY created_at DESC");

        // Initialiser un tableau pour stocker les objets Post.
        $posts = [];

        // Parcourir chaque ligne retournée par la requête.
        foreach ($results as $row) {
            $posts[] = $this->createPostFromResult($row);
        }

        return $posts;

    }//end findAll()


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

        // Exécute la requête avec l'ID du post.
        $success = $this->dbi->execute($stmt, [':post_id' => $postId]);

        if ($success === false) {
            throw new Exception('Erreur lors de l\'exécution de la requête.');
        }

        // Récupère le premier résultat.
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Vérifie si un résultat est trouvé, sinon retourne null.
        if ($result === false) {
            return null;
        }

        // Retourne l'objet Post créé à partir des résultats.
        return $this->createPostFromResult($result);

    }//end findById()


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
        $post->setValidator($this->validator);

        // Validation avant l'insertion.
        $violations = $post->validate();
        if ($violations->count() > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new Exception('Erreur de validation : '.implode(', ', $messages));
        }

        // Récupérer l'heure actuelle avec le fuseau horaire correct.
        $createdAt = new DateTime('now', new \DateTimeZone('Europe/Paris'));

        // La requête SQL pour insérer un nouvel article.
        $sql = "INSERT INTO `post` (`title`, `chapo`, `content`, `author`, `created_at`) 
        VALUES (:title, :chapo, :content, :author, :created_at)";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison des valeurs à la requête préparée.
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':chapo', $post->getChapo());
        $stmt->bindValue(':content', $post->getContent());
        $stmt->bindValue(':author', $post->getAuthor());
        $stmt->bindValue(':created_at', $createdAt->format('Y-m-d H:i:s'));

        // Exécution de la requête.
        if ($this->dbi->execute($stmt) === false) {
            throw new Exception("Failed to insert the post into the database.");
        }

        // Récupération et définition de l'ID de la dernière ligne insérée.
        $post->setId((int) $this->dbi->lastInsertId());
        $post->setCreatedAt($createdAt);
        // Met à jour l'objet Post avec la date correcte.
        return $post;

    }//end createPost()


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
        $sql = "UPDATE post SET title = :title, chapo = :chapo, content = :content, 
                author = :author, updated_at = :updated_at 
                WHERE post_id = :post_id";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison des valeurs à la requête préparée.
        $stmt->bindValue(':title', $post->getTitle());
        $stmt->bindValue(':chapo', $post->getChapo());
        $stmt->bindValue(':content', $post->getContent());
        $stmt->bindValue(':author', $post->getAuthor());
        // Utilisation de la date actuelle pour `updated_at`.
        $updatedAt = new DateTime('now', new \DateTimeZone('Europe/Paris'));
        $stmt->bindValue(':updated_at', $updatedAt->format('Y-m-d H:i:s'));
        $stmt->bindValue(':post_id', $post->getId());

        // Exécution de la requête.
        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to update the post in the database.");
        }

        return true;

    }//end updatePost()


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
        $sql = "DELETE FROM post WHERE post_id = :post_id";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface.
        $stmt = $this->dbi->prepare($sql);

        // Liaison de l'identifiant à la requête préparée.
        $stmt->bindValue(':post_id', $postId);

        // Exécution de la requête.
        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to delete the post from the database.");
        }

        return true;

    }//end deletePost()


    /**
     * Récupère les derniers articles par date de création.
     *
     * @param int $limit Le nombre maximum d'articles à retourner.
     *
     * @return Post[] Un tableau d'objets Post.
     */
    public function findLatest(int $limit=5): array
    {
        $sql     = "SELECT * FROM post ORDER BY created_at DESC LIMIT ".intval($limit);
        $results = $this->dbi->query($sql);

        $posts = [];
        foreach ($results as $row) {
            $posts[] = $this->createPostFromResult($row);
        }

        return $posts;

    }//end findLatest()


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

    }//end createPostFromResult()


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
            if (array_key_exists($field, $row) === false || $row[$field] === '' || $row[$field] === null) {
                throw new InvalidArgumentException("Le champ {$field} est requis.");
            }
        }

    }//end validateRow()


    /**
     * Construit une instance de Post à partir de la ligne de données.
     *
     * @param array $row La ligne de données contenant les informations de l'article.
     *
     * @return Post L'instance de Post créée.
     */
    private function buildPostFromRow(array $row): Post
    {
        $postData = [
            'postId' => (int) $row['post_id'],
            'title'  => $row['title'],
            'chapo'  => $row['chapo'],
            'content' => $row['content'],
            'author' => (isset($row['author']) === true) ? (int) $row['author'] : null,
            'createdAt' => new DateTime($row['created_at']),
            'updatedAt' => (isset($row['updated_at']) === true) ? new DateTime($row['updated_at']) : null,
        ];

        return new Post($postData, $this->validator);

    }//end buildPostFromRow()


}//end class
