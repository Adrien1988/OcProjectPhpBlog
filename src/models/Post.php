<?php

namespace App\Models;

use DateTime;

/**
 * Classe Post représentant une entité pour les articles dans la base de données.
 */
class Post
{

    /**
     * The unique identifier of the post.
     *
     * @var integer
     */
    private int $postId;

    /**
     * The title of the post.
     *
     * @var string
     */
    private string $title;

    /**
     * The introductory paragraph of the post.
     *
     * @var string
     */
    private string $chapo;

    /**
     * The main content of the post.
     *
     * @var string
     */
    private string $content;

    /**
     * The identifier of the author who wrote the post.
     *
     * @var integer
     */
    private int $author;

    /**
     * The date and time when the post was created.
     *
     * @var DateTime
     */
    private DateTime $createdAt;

    /**
     * The date and time when the post was last updated, can be null.
     *
     * @var ?DateTime
     */
    private ?DateTime $updatedAt = null;


    /**
     * Gets the unique identifier of the post.
     *
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;

    }//end getPostId()


    /**
     * Sets the unique identifier of the post.
     *
     * @param  int $postId the unique identifier of the post.
     * @return void
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;

    }//end setPostId()


    /**
     * Gets the title of the post.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;

    }//end getTitle()


    /**
     * Sets the title of the post.
     *
     * @param  string $title the title of the post.
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;

    }//end setTitle()


    /**
     * Gets the introductory paragraph of the post.
     *
     * @return string
     */
    public function getChapo(): string
    {
        return $this->chapo;

    }//end getChapo()


    /**
     * Sets the introductory paragraph of the post.
     *
     * @param  string $chapo the introductory paragraph of the post.
     * @return void
     */
    public function setChapo(string $chapo): void
    {
        $this->chapo = $chapo;

    }//end setChapo()


    /**
     * Gets the main content of the post.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;

    }//end getContent()


    /**
     * Sets the main content of the post.
     *
     * @param  string $content the main content of the post.
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


    /**
     * Gets the identifier of the author who wrote the post.
     *
     * @return int
     */
    public function getAuthor(): int
    {
        return $this->author;

    }//end getAuthor()


    /**
     * Sets the identifier of the author who wrote the post.
     *
     * @param  int $author the identifier of the author who wrote the post.
     * @return void
     */
    public function setAuthor(int $author): void
    {
        $this->author = $author;

    }//end setAuthor()


    /**
     * Gets the date and time when the post was created.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;

    }//end getCreatedAt()


    /**
     * Sets the date and time when the post was created.
     *
     * @param  DateTime $createdAt the date and time when the post was created.
     * @return void
     */
    public function setCreatedAt(DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;

    }//end setCreatedAt()


    /**
     * Gets the date and time when the post was last updated.
     *
     * @return ?DateTime
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;

    }//end getUpdatedAt()


    /**
     * Sets the date and time when the post was last updated.
     *
     * @param  ?DateTime $updatedAt the date and time when the post was last updated.
     * @return void
     */
    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;

    }//end setUpdatedAt()


}//end class
