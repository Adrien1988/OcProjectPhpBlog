<?php

namespace App\Models;

use App\Models\Traits\IdTrait;
use App\Models\Traits\TimestampableTrait;
use App\Models\traits\AuthTrait;

class Comment
{

    use IdTrait, TimestampableTrait, AuthTrait;

    /**
     * The content of the comment.
     *
     * @var string
     */
    private string $content;

    /**
     * Indicates whether the comment has been validated.
     *
     * @var boolean
     */
    private bool $isValidated;


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
     * @param string $content the content of the comment.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


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
     * @param bool $isValidated whether the comment has been validated.
     *
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
     * @param int $postId The ID of the post.
     *
     * @return void
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;

    }//end setPostId()


}//end class
