<?php

namespace Models;

use App\Models\User;
use App\Core\Database\DatabaseInterface;
use DateTime;

class UsersRepository
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

    /**
     * Récupère tous les utilisateurs de la base de données.
     *
     * @return User[] Liste des utilisateurs.
     */
    public function findAll(): array
    {
        // 'query' retourne maintenant un Iterator
        $results = $this->db->query("SELECT * FROM user");
        // Initialiser un tableau pour stocker les objets Users
        $users = [];

        // Parcourir chaque ligne retournée par la requête
        foreach ($results as $row) {
            // Transforme la ligne en objet User et l'ajoute au tableau
            $users[] = $this->createUserFromResult($row);
        }
        return $users;
    }

    /**
     * Récupère un utilisateur par son identifiant.
     *
     * @param int $userId L'identifiant de l'utilisateur à récupérer.
     * @return User|null Retourne l'objet User si trouvé, sinon null.
     */
    public function findById(int $userId): ?User
    {
        // Prépare et exécute la requête pour obtenir un seul enregistrement basé sur l'ID
        $result = $this->db->prepare("SELECT * FROM user WHERE user_id = :id", ['id' => $userId]);

        // Vérifie si le résultat contient au moins un enregistrement
        if (!empty($result)) {
            // Utilise createUserFromResult pour transformer le premier enregistrement trouvé en objet User
            return $this->createUserFromResult($result[0]);
        }

        // Retourne null si aucun enregistrement n'est trouvé
        return null;
    }

    /**
     * Récupère un utilisateur par son adresse email utilisant une requête itérative.
     *
     * @param string $email L'adresse email de l'utilisateur à récupérer.
     * @return User|null Retourne l'objet User si trouvé, sinon null.
     */
    public function findByEmail(string $email): ?User
    {
        $results = $this->db->query("SELECT * FROM users WHERE email = :email", [':email' => $email]);

        foreach ($results as $result) {
            if ($result) {
                // Utilise createUserFromResult pour transformer le premier enregistrement trouvé en objet User
                return $this->createUserFromResult($result);
            }
        }
        // Retourne null si aucun enregistrement n'est trouvé
        return null;
    }

    /**
     * Insère un nouvel utilisateur dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour insérer un nouvel utilisateur dans la base de données,
     * lie les valeurs de l'utilisateur à la requête pour éviter les injections SQL, et exécute la requête.
     * Après l'insertion, elle récupère l'ID de la ligne insérée et le définit sur l'objet User.
     *
     * @param User $user L'objet User à insérer dans la base de données.
     * @return User Retourne l'objet User avec l'identifiant attribué après l'insertion.
     * @throws \Exception Si l'insertion échoue pour une raison quelconque.
     */
    public function createUser(User $user): User
    {
        // La requête SQL pour insérer un nouvel user
        $sql = "INSERT INTO users (last_name, first_name, email, password, role, created_at, updated_at, token, expire_at) VALUES (:last_name, :first_name, :email, :password, :role, :created_at, :updated_at, :token, :expire_at)";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface
        $stmt = $this->db->prepare($sql);

        // Liaison des valeurs à la requête préparée
        $stmt->bindValue(':last_name', $user->getLastName());
        $stmt->bindValue(':first_name', $user->getFirstName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $user->getUpdatedAt() ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':token', $user->getToken());
        $stmt->bindValue(':expire_at', $user->getExpireAt()->format('Y-m-d H:i:s'));

        // Exécution de la requête
        if (!$this->db->execute($stmt, [])) {
            throw new \Exception("Failed to insert the user into the database.");
        }

        // Récupération et définition de l'ID de la dernière ligne insérée
        $user->setUserId((int) $this->db->lastInsertId());

        return $user;
    }

    /**
     * Met à jour un utilisateur existant dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour mettre à jour un utilisateur spécifique,
     * lie les valeurs de l'utilisateur à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle met à jour toutes les propriétés modifiables de l'utilisateur.
     *
     * @param User $user L'objet User à mettre à jour dans la base de données.
     * @return bool Retourne true si la mise à jour a réussi, sinon false.
     * @throws \Exception Si la mise à jour échoue pour une raison quelconque.
     */
    public function updateUser(User $user): bool
    {
        // La requête SQL pour mettre à jour un utilisateur existant
        $sql = "UPDATE users SET last_name = :last_name, first_name = :first_name, email = :email, 
         password = :password, role = :role, created_at = :created_at, updated_at = :updated_at, 
         token = :token, expire_at = :expire_at WHERE user_id = :user_id";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface
        $stmt = $this->db->prepare($sql);

        // Liaison des valeurs à la requête préparée
        $stmt->bindValue(':last_name', $user->getLastName());
        $stmt->bindValue(':first_name', $user->getFirstName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());  // Assurez-vous que le mot de passe est déjà haché
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $user->getUpdatedAt() ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':token', $user->getToken());
        $stmt->bindValue(':expire_at', $user->getExpireAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':user_id', $user->getUserId());

        // Exécution de la requête
        if (!$this->db->execute($stmt, [])) {  // Utilisation de la méthode execute de l'interface
            throw new \Exception("Failed to update the user in the database.");
        }

        return true;
    }

    /**
     * Supprime un utilisateur existant dans la base de données basé sur son identifiant.
     *
     * Cette méthode prépare une requête SQL pour supprimer un utilisateur,
     * lie l'identifiant de l'utilisateur à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle est sécurisée et ne permet que la suppression par identifiant.
     *
     * @param int $userId L'identifiant de l'utilisateur à supprimer.
     * @return bool Retourne true si la suppression a réussi, sinon false.
     * @throws \Exception Si la suppression échoue pour une raison quelconque.
     */
    public function deleteUser(int $userId): bool
    {
        // La requête SQL pour supprimer un utilisateur
        $sql = "DELETE FROM users WHERE user_id = :user_id";

        // Préparation de la requête SQL à l'aide de la méthode prepare de l'interface DatabaseInterface
        $stmt = $this->db->prepare($sql);

        // Liaison de l'identifiant à la requête préparée
        $stmt->bindValue(':user_id', $userId);

        // Exécution de la requête
        if (!$this->db->execute($stmt, [])) {  // Utilisation de la méthode execute de l'interface
            throw new \Exception("Failed to delete the user from the database.");
        }

        return true;
    }

    /**
     * Crée une instance de User à partir d'un tableau de données.
     * Cette méthode vérifie que toutes les données requises sont présentes
     * et utilise des paramètres nommés pour plus de clarté lors de la création de l'objet User.
     *
     * @param array $row Les données de l'utilisateur extraites de la base de données.
     * @return User L'instance de User créée, ou null si les données essentielles manquent.
     * @throws InvalidArgumentException Si des données obligatoires sont manquantes.
     */
    private function createUserFromResult(array $row): ?User
    {
        // Vérification de la présence de tous les champs requis dans la ligne de données
        if (
            empty($row['user_id']) || empty($row['last_name']) || empty($row['first_name']) || empty($row['email']) || empty($row['password']) || empty($row['role']) || empty($row['created_at']) || empty($row['expire_at'])
        ) {
            throw new \InvalidArgumentException("All fields are required except 'token' and 'updated_at', which can be null.");
        }

        // Création de l'instance de User avec les données récupérées
        return new User(
            userId: (int) $row['user_id'],
            lastName: $row['last_name'],
            firstName: $row['first_name'],
            email: $row['email'],
            password: $row['password'],
            role: $row['role'],
            createdAt: new DateTime($row['created_at']),
            updatedAt: isset($row['updated_at']) ? new DateTime($row['updated_at']) : null,
            token: $row['token'] ?? '',
            expireAt: new DateTime($row['expire_at'])
        );
    }
}
