<?php

namespace App\Models;

use DateTime;

/**
 * Classe User représentant une entité pour les utilisateurs dans la base de données.
 */
class User
{
    /**
     * The unique identifier of the user.
     *
     * @var integer
     */
    private int $userId;

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
     * The date and time when the user account was created.
     *
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * The date and time when the user account was last updated, can be null.
     *
     * @var ?DateTime
     */
    private ?DateTime $updatedAt = null;

    /**
     * The authentication token for the user.
     *
     * @var string
     */
    private string $token;

    /**
     * The date and time when the user's session or token expires.
     *
     * @var DateTime
     */
    private DateTime $expireAt;


    /**
     * Gets the unique identifier of the user.
     *
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }


    /**
     * Sets the unique identifier of the user.
     *
     * @param int $userId The unique identifier of the user.
     * @return void
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * Gets the last name of the user.
     *
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Sets the last name of the user.
     *
     * @param string $lastName The last name of the user.
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * Gets the first name of the user.
     *
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Sets the first name of the user.
     *
     * @param string $firstName The first name of the user.
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Gets the email address of the user.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the email address of the user.
     *
     * @param string $email The email address of the user.
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * Gets the hashed password of the user.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Sets the hashed password of the user.
     *
     * @param string $password The hashed password of the user.
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * Gets the role of the user (e.g., admin, user).
     *
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * Sets the role of the user (e.g., admin, user).
     *
     * @param string $role The role of the user.
     * @return void
     */
    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    /**
     * Gets the date and time when the user account was created.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the date and time when the user account was created.
     *
     * @param DateTime $createdAt The date and time when the user account was created.
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Gets the date and time when the user account was last updated.
     *
     * @return ?DateTime
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Sets the date and time when the user account was last updated.
     *
     * @param ?DateTime $updatedAt The date and time when the user account was last updated.
     * @return void
     */
    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Gets the authentication token for the user.
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Sets the authentication token for the user.
     *
     * @param string $token The authentication token for the user.
     * @return void
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * Gets the date and time when the user's session or token expires.
     *
     * @return DateTime
     */
    public function getExpireAt(): DateTime
    {
        return $this->expireAt;
    }

    /**
     * Sets the date and time when the user's session or token expires.
     *
     * @param DateTime $expireAt The date and time when the user's session or token expires.
     * @return void
     */
    public function setExpireAt(DateTime $expireAt): void
    {
        $this->expireAt = $expireAt;
    }
}
