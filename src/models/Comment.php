<?php

namespace App\Models;

use DateTime;

class Comment
{
    /**
     * @var integer $commentId
     *
     * The ID of the comment.
     */
    private int $commentId;

    /**
     * @var string $content
     *
     * The content of the comment.
     */
    private string $content;

    /**
     * @var DateTime $createdAt
     *
     * The date and time when the comment was created.
     */
    private DateTime $createdAt;

    /**
     * @var boolean $isValidated
     *
     * Indicates whether the comment has been validated.
     */
    private bool $isValidated;

    /**
     * @var integer $postId
     *
     * The ID of the post to which the comment belongs.
     */
    private int $postId;

    /**
     * @var integer $author
     *
     * The ID of the author who wrote the comment.
     */
    private int $author;

    // Getters et Setters

    public function getCommentId(): int
    {
        return $this->commentId;
    }

    public function setCommentId(int $commentId): void
    {
        $this->commentId = $commentId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getIsValidated(): bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): void
    {
        $this->isValidated = $isValidated;
    }

    public function getPostId(): int
    {
        return $this->postId;
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getAuthor(): int
    {
        return $this->author;
    }

    public function setAuthor(int $author): void
    {
        $this->author = $author;
    }
}
