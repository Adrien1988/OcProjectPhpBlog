<?php

namespace App\Models;

use DateTime;

/**
 * Class Comment
 *
 * Représente un commentaire dans le système.
 */
class Comment
{

    /**
     * The ID of the comment.
     *
     * @var integer
     */
    private int $commentId;

    /**
     * The content of the comment.
     *
     * @var string
     */
    private string $content;

    /**
     * The date and time when the comment was created.
     *
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * Indicates whether the comment has been validated.
     *
     * @var boolean
     */
    private bool $isValidated;

    /**
     * The ID of the post to which the comment belongs.
     *
     * @var integer
     */
    private int $postId;

    /**
     * The ID of the author who wrote the comment.
     *
     * @var integer
     */
    private int $author;


    /**
     * Gets the ID of the comment.
     *
     * @return int
     */
    public function getCommentId(): int
    {
        return $this->commentId;

    }//end getCommentId()


    /**
     * Sets the ID of the comment.
     *
     * @param  int $commentId the ID of the comment.
     * @return void
     */
    public function setCommentId(int $commentId): void
    {
        $this->commentId = $commentId;

    }//end setCommentId()


    /**
     * Gets the content of the comment.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;

    }//end getContent()


    /**
     * Sets the content of the comment.
     *
     * @param  string $content the content of the comment.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


    /**
     * Gets the date and time when the comment was created.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;

    }//end getCreatedAt()


    /**
     * Sets the date and time when the comment was created.
     *
     * @param  DateTime $createdAt the date and time when the comment was created.
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;

    }//end setCreatedAt()


    /**
     * Gets whether the comment has been validated.
     *
     * @return bool
     */
    public function isValidated(): bool
    {
        return $this->isValidated;

    }//end isValidated()


    /**
     * Sets whether the comment has been validated.
     *
     * @param  bool $isValidated whether the comment has been validated.
     * @return void
     */
    public function setIsValidated(bool $isValidated): void
    {
        $this->isValidated = $isValidated;

    }//end setIsValidated()


    /**
     * Gets the ID of the post to which the comment belongs.
     *
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;

    }//end getPostId()


    /**
     * Sets the ID of the post to which the comment belongs.
     *
     * @param  int $postId the ID of the post to which the comment belongs.
     * @return void
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;

    }//end setPostId()


    /**
     * Gets the ID of the author who wrote the comment.
     *
     * @return int
     */
    public function getAuthor(): int
    {
        return $this->author;

    }//end getAuthor()


    /**
     * Sets the ID of the author who wrote the comment.
     *
     * @param  int $author the ID of the author who wrote the comment.
     * @return void
     */
    public function setAuthor(int $author): void
    {
        $this->author = $author;

    }//end setAuthor()


}//end class
