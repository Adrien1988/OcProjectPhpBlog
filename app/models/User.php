<?php

namespace  App\Models;

use DateTime;

class User
{
    /**
 * @var integer $userId
 * 
 * The unique identifier of the user.
 */
private int $userId;

/**
 * @var string $lastName
 * 
 * The last name of the user.
 */
private string $lastName;

/**
 * @var string $firstName
 * 
 * The first name of the user.
 */
private string $firstName;

/**
 * @var string $email
 * 
 * The email address of the user.
 */
private string $email;

/**
 * @var string $password
 * 
 * The hashed password of the user.
 */
private string $password;

/**
 * @var string $role
 * 
 * The role of the user (e.g., admin, user).
 */
private string $role;

/**
 * @var DateTime $createdAt
 * 
 * The date and time when the user account was created.
 */
private DateTime $createdAt;

/**
 * @var ?DateTime $updatedAt
 * 
 * The date and time when the user account was last updated, can be null.
 */
private ?DateTime $updatedAt = null;

/**
 * @var string $token
 * 
 * The authentication token for the user.
 */
private string $token;

/**
 * @var DateTime $expireAt
 * 
 * The date and time when the user's session or token expires.
 */
private DateTime $expireAt;


    // Getters et Setters
    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void 
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getToken(): string{
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getExpireAt(): DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(DateTime $expireAt): void
    {
        $this->expireAt = $expireAt;
    }
}
