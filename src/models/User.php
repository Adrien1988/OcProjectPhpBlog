<?php

namespace App\Models;

use DateTime;
use App\Models\Traits\IdTrait;
use App\Models\traits\AuthTrait;
use App\Models\Traits\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe User représentant une entité pour les utilisateurs.
 */
class User
{

    use IdTrait, TimestampableTrait, AuthTrait;

    /**
     * Le nom de famille de l'utilisateur.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le nom de famille est requis.")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le nom doit contenir au moins {{ limit }} caractères.")]
    private string $lastName;

    /**
     * Le prénom de l'utilisateur.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le prénom est requis.")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le prénom doit contenir au moins {{ limit }} caractères.")]
    private string $firstName;

    /**
     * L'email de l'utilisateur.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: "L'adresse email n'est pas valide.")]
    private string $email;

    /**
     * Le mot de passe de l'utilisateur.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le mot de passe est requis.")]
    #[Assert\Length(min: 8, minMessage: "Le mot de passe doit contenir au moins {{ limit }} caractères.")]
    private string $password;

    /**
     * Le rôle de l'utilisateur. (e.g., admin, user).
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le rôle est requis.")]
    private string $role;

    /**
     * Le token d'autentification de l'utilisateur.
     *
     * @var string|null
     */
    private ?string $token = null;

    /**
     * La date et l'heure d'expiration de la session ou du jeton de l'utilisateur.
     *
     * @var DateTime|null
     */
    private ?DateTime $expireAt = null;

    /**
     * Le jeton de réinitialisation du mot de passe.
     *
     * @var string|null
     */
    private ?string $pwdResetToken = null;

    /**
     * La date et l'heure d'expiration du jeton de réinitialisation du mot de passe.
     *
     * @var DateTime|null
     */
    private ?DateTime $pwdResetExpiresAt = null;


    /**
     * Constructeur de la classe User.
     *
     * @param array $userData Tableau contenant les données de l'utilisateur :
     *                        - 'userId' : int|null, l'ID de l'utilisateur.
     *                        - 'lastName' : string, le nom de famille.
     *                        - 'firstName' : string, le prénom.
     *                        - 'email' : string, l'adresse email.
     *                        - 'password' : string, le mot de passe hashé.
     *                        - 'role' : string, le rôle de l'utilisateur.
     *                        - 'createdAt' : string, la date de création.
     *                        - 'updatedAt' : string|null, la date de mise à jour.
     *                        - 'token' : string|null, le token d'authentification.
     *                        - 'expireAt' : string|null, la date d'expiration du token.
     *                        - 'pwdResetToken' : string|null, le token de réinitialisation.
     *                        - 'pwdResetExpiresAt' : string|null, la date d'expiration du token de réinitialisation.
     */
    public function __construct(array $userData)
    {
        $this->setId(($userData['userId'] ?? null));
        $this->setLastName($userData['lastName']);
        $this->setFirstName($userData['firstName']);
        $this->setEmail($userData['email']);
        $this->setPassword($userData['password']);
        $this->setRole($userData['role']);
        $this->setCreatedAt(new DateTime($userData['createdAt']));

        // Vérifier et convertir updatedAt uniquement si défini.
        $updatedAt = ($userData['updatedAt'] ?? null);
        $this->setUpdatedAt($updatedAt instanceof DateTime ? $updatedAt : ($updatedAt !== null ? new DateTime($updatedAt) : null));

        $this->setToken(($userData['token'] ?? null));
        $this->setExpireAt($userData['expireAt'] !== null ? new DateTime($userData['expireAt']) : null);
        $this->setPwdResetToken(($userData['pwdResetToken'] ?? null));
        $this->setPwdResetExpiresAt($userData['pwdResetExpiresAt'] !== null ? new DateTime($userData['pwdResetExpiresAt']) : null);

    }//end __construct()


    /**
     * Retourne le nom de famille de l'utilisateur.
     *
     * @return string Le nom de famille de l'utilisateur.
     */
    public function getLastName(): string
    {
        return $this->lastName;

    }//end getLastName()


    /**
     * Définit le nom de famille de l'utilisateur.
     *
     * @param string $lastName Le nom de famille.
     *
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;

    }//end setLastName()


    /**
     * Retourne le prénom de l'utilisateur.
     *
     * @return string Le prénom de l'utilisateur.
     */
    public function getFirstName(): string
    {
        return $this->firstName;

    }//end getFirstName()


    /**
     * Définit le prénom de l'utilisateur.
     *
     * @param string $firstName Le prénom.
     *
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;

    }//end setFirstName()


    /**
     * Retourne l'adresse email de l'utilisateur.
     *
     * @return string L'adresse email de l'utilisateur.
     */
    public function getEmail(): string
    {
        return $this->email;

    }//end getEmail()


    /**
     * Définit l'adresse email de l'utilisateur.
     *
     * @param string $email L'adresse email.
     *
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;

    }//end setEmail()


    /**
     * Retourne le mot de passe hashé de l'utilisateur.
     *
     * @return string Le mot de passe hashé.
     */
    public function getPassword(): string
    {
        return $this->password;

    }//end getPassword()


    /**
     * Définit le mot de passe hashé de l'utilisateur.
     *
     * @param string $password Le mot de passe hashé.
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;

    }//end setPassword()


    /**
     * Retourne le rôle de l'utilisateur.
     *
     * @return string Le rôle de l'utilisateur.
     */
    public function getRole(): string
    {
        return $this->role;

    }//end getRole()


    /**
     * Définit le rôle de l'utilisateur.
     *
     * @param string $role Le rôle.
     *
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;

    }//end setRole()


    /**
     * Vérifie si l'utilisateur a le rôle d'administrateur.
     *
     * @return bool Retourne true si l'utilisateur est un administrateur, sinon false.
     */
    public function isAdmin(): bool
    {
        return strtolower($this->role) === 'admin';

    }//end isAdmin()


    /**
     * Gets the authentication token for the user.
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->token;

    }//end getToken()


    /**
     * Sets the authentication token for the user.
     *
     * @param string|null $token The authentication token for the user.
     *
     * @return void
     */
    public function setToken(?string $token): void
    {
        $this->token = $token;

    }//end setToken()


    /**
     * Gets the date and time when the user's session or token expires.
     *
     * @return DateTime|null
     */
    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;

    }//end getExpireAt()


    /**
     * Sets the date and time when the user's session or token expires.
     *
     * @param DateTime|null $expireAt The date and time when the user's session or token expires.
     *
     * @return void
     */
    public function setExpireAt(?DateTime $expireAt): void
    {
        $this->expireAt = $expireAt;

    }//end setExpireAt()


    /**
     * Gets the password reset token.
     *
     * @return string|null
     */
    public function getPwdResetToken(): ?string
    {
        return $this->pwdResetToken;

    }//end getPwdResetToken()


    /**
     * Sets the password reset token.
     *
     * @param string|null $token The password reset token.
     *
     * @return void
     */
    public function setPwdResetToken(?string $token): void
    {
        $this->pwdResetToken = $token;

    }//end setPwdResetToken()


    /**
     * Gets the password reset expiration date.
     *
     * @return DateTime|null
     */
    public function getPwdResetExpiresAt(): ?DateTime
    {
        return $this->pwdResetExpiresAt;

    }//end getPwdResetExpiresAt()


    /**
     * Sets the password reset expiration date.
     *
     * @param DateTime|null $expiresAt The expiration date.
     *
     * @return void
     */
    public function setPwdResetExpiresAt(?DateTime $expiresAt): void
    {
        $this->pwdResetExpiresAt = $expiresAt;

    }//end setPwdResetExpiresAt()


}//end class
