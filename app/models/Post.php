<?php

namespace App\Models;

use DateTime;

/**
 * Classe Post représentant une entité pour les articles dans la base de données.
 */
class Post
{
    /**
     * @var integer $postId
     *
     * The unique identifier of the post.
     */
    private int $postId;

    /**
     * @var string $title
     *
     * The title of the post.
     */     
    private string $title;

    /**
     * @var string $chapo
     *
     * The introductory paragraph of the post.
     */     
    private string $chapo;

    /**
     * @var string $content
     *
     * The main content of the post.
     */     
    private string $content;

    /**
     * @var integer $author
     *
     * The identifier of the author who wrote the post.
     */     
    private int $author;

    /**
     * @var DateTime $createdAt
     *
     * The date and time when the post was created.
     */     
    private DateTime $createdAt;

    /**
     * @var ?DateTime $updatedAt
     *
     * The date and time when the post was last updated, can be null.
     */     
    private ?DateTime $updatedAt = null;


    // Getters and Setters

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getChapo(): string
    {
        return $this->chapo;
    }

    public function setChapo(string $chapo): void
    {
        $this->chapo = $chapo;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getAuthor(): int
    {
        return $this->author;
    }

    public function setAuthor(int $author): void
    {
        $this->author = $author;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
