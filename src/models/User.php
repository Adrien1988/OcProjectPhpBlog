<?php

namespace App\Models;

use App\Models\Traits\IdTrait;
use App\Models\Traits\TimestampableTrait;
use App\Models\traits\AuthTrait;
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
     * @param array              $userData  Tableau contenant les données de l'utilisateur.
     * @param ValidatorInterface $validator Le validateur Symfony à injecter.
     */
    public function __construct(array $userData, ValidatorInterface $validator)
    {
        $this->setId(($userData['userId'] ?? null));
        $this->lastName  = $userData['lastName'];
        $this->firstName = $userData['firstName'];
        $this->email     = $userData['email'];
        $this->password  = $userData['password'];
        $this->role      = $userData['role'];
        $this->setCreatedAt($userData['createdAt']);
        $this->setUpdatedAt((isset($userData['updatedAt']) === true) ? $userData['updatedAt'] : null);
        $this->token     = ($userData['token'] ?? null);
        $this->expireAt  = ($userData['expireAt'] ?? null);
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
