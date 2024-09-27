<?php

namespace App\Models;

use DateTime;
use App\Models\Traits\IdTrait;
use App\Models\traits\AuthTrait;
use App\Models\Traits\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Classe User représentant une entité pour les utilisateurs dans la base de données.
 */
class User
{

    use IdTrait, TimestampableTrait, AuthTrait;

    /**
     * Le validateur Symfony.
     *
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * The last name of the user.
     *
     * @var string
     */
    private string $lastName;

    /**
     * The first name of the user.
     *
     * @var string
     */
    private string $firstName;

    /**
     * The email address of the user.
     *
     * @var string
     */
    private string $email;

    /**
     * The hashed password of the user.
     *
     * @var string
     */
    private string $password;

    /**
     * The role of the user (e.g., admin, user).
     *
     * @var string
     */
    private string $role;

    /**
     * The authentication token for the user.
     *
     * @var string
     */
    private ?string $token;

    /**
     * The date and time when the user's session or token expires.
     *
     * @var DateTime
     */
    private ?DateTime $expireAt;


    /**
     * Constructeur pour la classe User.
     *
     * @param int                $userId    L'identifiant unique de l'utilisateur.
     * @param string             $lastName  Le nom de famille de l'utilisateur.
     * @param string             $firstName Le prénom de
     *                                      l'utilisateur.
     * @param string             $email     L'adresse e-mail de l'utilisateur.
     * @param string             $password  Le mot de passe de l'utilisateur.
     * @param string             $role      Le rôle de
     *                                      l'utilisateur.
     * @param DateTime           $createdAt La date de création de
     *                                      l'utilisateur.
     * @param DateTime|null      $updatedAt La date de mise à jour de l'utilisateur (peut être
     *                                      null).
     * @param string|null        $token     Le jeton d'authentification de l'utilisateur (peut être
     *                                      null).
     * @param DateTime|null      $expireAt  La date d'expiration du jeton (peut être
     *                                      null).
     * @param ValidatorInterface $validator L'instance du validateur Symfony.
     */
    public function __construct(int $userId, string $lastName, string $firstName, string $email, string $password, string $role, DateTime $createdAt, ?DateTime $updatedAt=null, ?string $token=null, ?DateTime $expireAt=null, ValidatorInterface $validator)
    {
        $this->setId($userId);
        $this->lastName  = $lastName;
        $this->firstName = $firstName;
        $this->email     = $email;
        $this->password  = $password;
        $this->role      = $role;
        $this->setCreatedAt($createdAt);
        $this->setUpdatedAt($updatedAt);
        $this->token     = $token;
        $this->expireAt  = $expireAt;
        $this->validator = $validator;

    }//end __construct()


    /**
     * Injecte le validateur Symfony dans l'entité User.
     *
     * @param ValidatorInterface $validator Le validateur Symfony à injecter.
     *
     * @return void
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;

    }//end setValidator()


    /**
     * Gets the last name of the user.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;

    }//end getLastName()


    /**
     * Sets the last name of the user.
     *
     * @param string $lastName The last name of the user.
     *
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;

    }//end setLastName()


    /**
     * Gets the first name of the user.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;

    }//end getFirstName()


    /**
     * Sets the first name of the user.
     *
     * @param string $firstName The first name of the user.
     *
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;

    }//end setFirstName()


    /**
     * Gets the email address of the user.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;

    }//end getEmail()


    /**
     * Sets the email address of the user.
     *
     * @param string $email The email address of the user.
     *
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;

    }//end setEmail()


    /**
     * Gets the hashed password of the user.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;

    }//end getPassword()


    /**
     * Sets the hashed password of the user.
     *
     * @param string $password The hashed password of the user.
     *
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;

    }//end setPassword()


    /**
     * Gets the role of the user (e.g., admin, user).
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;

    }//end getRole()


    /**
     * Sets the role of the user (e.g., admin, user).
     *
     * @param string $role The role of the user.
     *
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;

    }//end setRole()


    /**
     * Gets the authentication token for the user.
     *
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;

    }//end getToken()


    /**
     * Sets the authentication token for the user.
     *
     * @param string $token The authentication token for the user.
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
     * @return DateTime
     */
    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;

    }//end getExpireAt()


    /**
     * Sets the date and time when the user's session or token expires.
     *
     * @param DateTime $expireAt The date and time when the user's session or token expires.
     *
     * @return void
     */
    public function setExpireAt(?DateTime $expireAt): void
    {
        $this->expireAt = $expireAt;

    }//end setExpireAt()


    /**
     * Validates the user entity.
     *
     * @return ConstraintViolationListInterface|null Returns the list of violations or null if there are none.
     */
    public function validate(): ?ConstraintViolationListInterface
    {
        $constraints = new Assert\Collection(
            [
                'lastName'   => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 50])],
                'firstName'  => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 50])],
                'email'      => [new Assert\NotBlank(), new Assert\Email()],
                'password'   => [new Assert\NotBlank(), new Assert\Length(['min' => 8])],
                'role'       => [new Assert\NotBlank()],
            ]
        );

        $data = [
            'lastName'  => $this->getLastName(),
            'firstName' => $this->getFirstName(),
            'email'     => $this->getEmail(),
            'password'  => $this->getPassword(),
            'role'      => $this->getRole(),
        ];

        return $this->validator->validate($data, $constraints);

    }//end validate()


}//end class
