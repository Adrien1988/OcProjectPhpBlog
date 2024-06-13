<?php

namespace Models;

use DateTime;
use Exception;
use App\Models\User;
use InvalidArgumentException;
use App\Core\Database\DatabaseInterface;

class UsersRepository
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
     * Récupère tous les utilisateurs de la base de données.
     *
     * @return User[] Liste des utilisateurs.
     */
    public function findAll(): array
    {
        $results = $this->dbi->query("SELECT * FROM user");

        $users = [];

        foreach ($results as $row) {
            $users[] = $this->createUserFromResult($row);
        }

        return $users;

    }//end findAll()


    /**
     * Récupère un utilisateur par son identifiant.
     *
     * @param int $userId L'identifiant de l'utilisateur à récupérer.
     *
     * @return User|null Retourne l'objet User si trouvé, sinon null.
     */
    public function findById(int $userId): ?User
    {
        $result = $this->dbi->prepare("SELECT * FROM user WHERE user_id = :id", ['id' => $userId]);

        if (empty($result) === false) {
            return $this->createUserFromResult($result[0]);
        }

        return null;

    }//end findById()


    /**
     * Récupère un utilisateur par son adresse email utilisant une requête itérative.
     *
     * @param string $email L'adresse email de l'utilisateur à récupérer.
     *
     * @return User|null Retourne l'objet User si trouvé, sinon null.
     */
    public function findByEmail(string $email): ?User
    {
        $results = $this->dbi->query("SELECT * FROM users WHERE email = :email", [':email' => $email]);

        foreach ($results as $result) {
            if ($result !== null) {
                return $this->createUserFromResult($result);
            }
        }

        return null;

    }//end findByEmail()


    /**
     * Insère un nouvel utilisateur dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour insérer un nouvel utilisateur dans la base de données,
     * lie les valeurs de l'utilisateur à la requête pour éviter les injections SQL, et exécute la requête.
     * Après l'insertion, elle récupère l'ID de la ligne insérée et le définit sur l'objet User.
     *
     * @param User $user L'objet User à insérer dans la base de données.
     *
     * @return User Retourne l'objet User avec l'identifiant attribué après l'insertion.
     *
     * @throws Exception Si l'insertion échoue pour une raison quelconque.
     */
    public function createUser(User $user): User
    {
        $sql = "INSERT INTO users (last_name, first_name, email, password, role, created_at, updated_at, token, expire_at) VALUES (:last_name, :first_name, :email, :password, :role, :created_at, :updated_at, :token, :expire_at)";

        $stmt = $this->dbi->prepare($sql);

        $stmt->bindValue(':last_name', $user->getLastName());
        $stmt->bindValue(':first_name', $user->getFirstName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $user->getUpdatedAt() !== null ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':token', $user->getToken());
        $stmt->bindValue(':expire_at', $user->getExpireAt()->format('Y-m-d H:i:s'));

        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to insert the user into the database.");
        }

        $user->setUserId((int) $this->dbi->lastInsertId());

        return $user;

    }//end createUser()


    /**
     * Met à jour un utilisateur existant dans la base de données.
     *
     * Cette méthode prépare une requête SQL pour mettre à jour un utilisateur spécifique,
     * lie les valeurs de l'utilisateur à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle met à jour toutes les propriétés modifiables de l'utilisateur.
     *
     * @param User $user L'objet User à mettre à jour dans la base de données.
     *
     * @return bool Retourne true si la mise à jour a réussi, sinon false.
     *
     * @throws Exception Si la mise à jour échoue pour une raison quelconque.
     */
    public function updateUser(User $user): bool
    {
        $sql = "UPDATE users SET last_name = :last_name, first_name = :first_name, email = :email, 
         password = :password, role = :role, created_at = :created_at, updated_at = :updated_at, 
         token = :token, expire_at = :expire_at WHERE user_id = :user_id";

        $stmt = $this->dbi->prepare($sql);

        $stmt->bindValue(':last_name', $user->getLastName());
        $stmt->bindValue(':first_name', $user->getFirstName());
        $stmt->bindValue(':email', $user->getEmail());
        $stmt->bindValue(':password', $user->getPassword());
        $stmt->bindValue(':role', $user->getRole());
        $stmt->bindValue(':created_at', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':updated_at', $user->getUpdatedAt() !== null ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null);
        $stmt->bindValue(':token', $user->getToken());
        $stmt->bindValue(':expire_at', $user->getExpireAt()->format('Y-m-d H:i:s'));
        $stmt->bindValue(':user_id', $user->getUserId());

        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to update the user in the database.");
        }

        return true;

    }//end updateUser()


    /**
     * Supprime un utilisateur existant dans la base de données basé sur son identifiant.
     *
     * Cette méthode prépare une requête SQL pour supprimer un utilisateur,
     * lie l'identifiant de l'utilisateur à la requête pour éviter les injections SQL,
     * et exécute la requête. Elle est sécurisée et ne permet que la suppression par identifiant.
     *
     * @param int $userId L'identifiant de l'utilisateur à supprimer.
     *
     * @return bool Retourne true si la suppression a réussi, sinon false.
     *
     * @throws Exception Si la suppression échoue pour une raison quelconque.
     */
    public function deleteUser(int $userId): bool
    {
        $sql = "DELETE FROM users WHERE user_id = :user_id";

        $stmt = $this->dbi->prepare($sql);

        $stmt->bindValue(':user_id', $userId);

        if ($this->dbi->execute($stmt, []) === false) {
            throw new Exception("Failed to delete the user from the database.");
        }

        return true;

    }//end deleteUser()


    /**
     * Crée un utilisateur à partir d'une ligne de données.
     *
     * @param array $row La ligne de données contenant les informations de l'utilisateur.
     *
     * @return User|null L'instance de User créée ou null en cas d'erreur.
     *
     * @throws InvalidArgumentException Si des champs obligatoires sont manquants.
     */
    private function createUserFromResult(array $row): ?User
    {
        $this->validateRow($row);

        return $this->buildUserFromRow($row);

    }//end createUserFromResult()


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
            'user_id',
            'last_name',
            'first_name',
            'email',
            'password',
            'role',
            'created_at',
            'expire_at',
        ];
        foreach ($requiredFields as $field) {
            if (empty($row[$field]) !== null) {
                throw new InvalidArgumentException("Tous les champs sauf 'updated_at' et 'token' sont requis.");
            }
        }

    }//end validateRow()


    /**
     * Construit une instance de User à partir de la ligne de données.
     *
     * @param array $row La ligne de données contenant les informations de l'utilisateur.
     *
     * @return User L'instance de User créée.
     */
    private function buildUserFromRow(array $row): User
    {
        return new User(
            userId: (int) $row['user_id'],
            lastName: $row['last_name'],
            firstName: $row['first_name'],
            email: $row['email'],
            password: $row['password'],
            role: $row['role'],
            createdAt: new DateTime($row['created_at']),
            updatedAt: isset($row['updated_at']) !== null ? new DateTime($row['updated_at']) : null,
            token: ($row['token'] ?? ''),
            expireAt: new DateTime($row['expire_at'])
        );

    }//end buildUserFromRow()


}//end class
