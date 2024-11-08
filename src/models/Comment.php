<?php

namespace App\Models;

use DateTime;
use App\Models\Traits\IdTrait;
use App\Models\Traits\AuthTrait;
use App\Models\Traits\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

class Comment
{

    use IdTrait, TimestampableTrait, AuthTrait;

    /**
     * Le contenu du commentaire.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le contenu du commentaire ne peut pas être vide.")]
    #[Assert\Length(
        min: 20,
        minMessage: "Le commentaire doit comporter au moins {{ limit }} caractères."
    )]
    private string $content;

    /**
     * Indique si le commentaire a été validé.
     *
     * @var boolean
     */
    private bool $isValidated;

    /**
     * L'ID du post associé.
     *
     * @var integer
     */
    #[Assert\NotBlank(message: "L'ID du post est requis.")]
    #[Assert\Type(
        type: 'integer',
        message: "L'ID du post doit être un entier."
    )]
    private int $postId;


    /**
     * Constructeur de la classe Comment.
     *
     * @param int|null $id          L'ID du commentaire (null si non défini).
     * @param string   $content     Le contenu du commentaire.
     * @param DateTime $createdAt   La date de création du commentaire.
     * @param bool     $isValidated Indique si le commentaire est validé.
     * @param int      $postId      L'ID du post associé.
     * @param int      $author      L'ID de l'auteur du commentaire.
     */
    public function __construct(
        ?int $commentId,
        string $content,
        DateTime $createdAt,
        bool $isValidated,
        int $postId,
        int $author
    ) {
        if ($commentId !== null) {
            $this->setId($commentId);
        }

        $this->setContent($content);
        $this->setCreatedAt($createdAt);
        $this->setIsValidated($isValidated);
        $this->setPostId($postId);
        $this->setAuthor($author);

    }//end __construct()


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
