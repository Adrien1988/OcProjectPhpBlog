<?php

namespace  App\Models;

use App\Core\Database\DatabaseInterface;

class UserModel
{
    private DatabaseInterface $db;

    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Récupère un utilisateur par son ID.
     * 
     * @param int $userId L'ID de l'utilisateur.
     * @return array|false Les données de l'utilisateur ou false si aucun utilisateur n'est trouvé.
     */
    public function getUserById(int $userId): array|false
    {
        $result = $this->db->prepare("SELECT * FROM user Where id = :id", 
        ['id' => $userId]);
        return $result ? $result[0]: false;
    }

    /**
     * Vérifie les informations de connexion d'un utilisateur.
     * 
     * Effectue une recherche dans la base de données pour un utilisateur avec un email spécifié,
     * puis vérifie si le mot de passe fourni correspond au mot de passe stocké en utilisant la fonction
     * password_verify. Cette méthode assume que `prepare` de `DatabaseInterface` exécute la requête
     * et renvoie les résultats directement pour la vérification.
     * 
     * @param string $email L'email de l'utilisateur.
     * @param string $password Le mot de passe de l'utilisateur.
     * @return array|false Les données de l'utilisateur si les informations de connexion sont correctes, sinon false.
     */
    public function checkCredentials(string $email, string $password): array|false {
        $result = $this->db->prepare("SELECT * FROM user WHERE email = ?", [$email]);

        if ($result && password_verify($password, $result[0]['password'])) {
            return $result[0];
        }
        return false;
    }
}
