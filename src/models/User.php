<?php

namespace App\Models;

use DateTime;
use App\Models\Traits\IdTrait;
use App\Models\traits\AuthTrait;
use App\Models\Traits\TimestampableTrait;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Classe User représentant une entité pour les utilisateurs dans la base de données.
 */
class User
{

    use IdTrait, TimestampableTrait, AuthTrait;

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
     * Gets the last name of the user.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    } //end getLastName()


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
    } //end setLastName()


    /**
     * Gets the first name of the user.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    } //end getFirstName()


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
    } //end setFirstName()


    /**
     * Gets the email address of the user.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    } //end getEmail()


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
    } //end setEmail()


    /**
     * Gets the hashed password of the user.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    } //end getPassword()


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
    } //end setPassword()


    /**
     * Gets the role of the user (e.g., admin, user).
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    } //end getRole()


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
    } //end setRole()


    /**
     * Gets the authentication token for the user.
     *
     * @return string
     */
    public function getToken(): ?string
    {
        return $this->token;
    } //end getToken()


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
    } //end setToken()


    /**
     * Gets the date and time when the user's session or token expires.
     *
     * @return DateTime
     */
    public function getExpireAt(): ?DateTime
    {
        return $this->expireAt;
    } //end getExpireAt()


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
    } //end setExpireAt()


    /**
     * Validates the user entity.
     *
     * @return ConstraintViolationListInterface|null Returns the list of violations or null if there are none.
     */
    public function validate(): ?ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();

        $constraints = new Assert\Collection([
            'lastName' => [
                new Assert\NotBlank(['message' => 'Le nom est requis.']),
                new Assert\Length(['max' => 50, 'maxMessage' => 'Le nom ne doit pas dépasser 50 caractères.']),
            ],
            'firstName' => [
                new Assert\NotBlank(['message' => 'Le prénom est requis.']),
                new Assert\Length(['max' => 50, 'maxMessage' => 'Le prénom ne doit pas dépasser 50 caractères.']),
            ],
            'email' => [
                new Assert\NotBlank(['message' => 'L\'adresse e-mail est requise.']),
                new Assert\Email(['message' => 'L\'adresse e-mail n\'est pas valide.']),
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Le mot de passe est requis.']),
                new Assert\Length(['min' => 8, 'minMessage' => 'Le mot de passe doit contenir au moins 8 caractères.']),
            ],
            'role' => [
                new Assert\NotBlank(['message' => 'Le rôle est requis.']),
            ],
            'token' => [
                new Assert\Optional([
                    new Assert\Length(['max' => 255, 'maxMessage' => 'Le token ne doit pas dépasser 255 caractères.']),
                ]),
            ],
            'expireAt' => [
                new Assert\Optional([
                    new Assert\DateTime(['message' => 'La date d\'expiration doit être une date valide.']),
                ]),
            ],
        ]);

        $data = [
            'lastName'  => $this->lastName,
            'firstName' => $this->firstName,
            'email'     => $this->email,
            'password'  => $this->password,
            'role'      => $this->role,
            'token'     => $this->token,
            'expireAt'  => $this->expireAt ? $this->expireAt->format('Y-m-d H:i:s') : null,
        ];

        return $validator->validate($data, $constraints);
    }
}//end class
