<?php

namespace App\Models;

use DateTime;
use App\Models\Traits\IdTrait;
use App\Models\Traits\AuthTrait;
use App\Models\Traits\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe Comment représentant une entité pour les commentaires.
 */
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
     * Le statut du commentaire (e.g., validé, en attente, rejeté).
     *
     * @var string
     */
    private string $status;

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
     * @param array $commentData Tableau contenant les données du commentaire :
     *                           - 'commentId' : int|null, l'ID du commentaire.
     *                           - 'content' : string, le contenu du commentaire.
     *                           - 'createdAt' : string, la date de création du commentaire.
     *                           - 'postId' : int, l'ID du post associé.
     *                           - 'author' : int, l'ID de l'auteur.
     *                           - 'status' : string, le statut du commentaire.
     */
    public function __construct(
        array $commentData
    ) {
        $this->setId(($commentData['commentId'] ?? null));
        $this->setContent($commentData['content']);
        $this->setCreatedAt(new DateTime($commentData['createdAt']));
        $this->setPostId($commentData['postId']);
        $this->setAuthor($commentData['author']);
        $this->setStatus($commentData['status']);

    }//end __construct()


    /**
     * Obtient le contenu du commentaire.
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;

    }//end getContent()


    /**
     * Définit le contenu du commentaire.
     *
     * @param string $content Le contenu du commentaire.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


    /**
     * Obtient le statut du commentaire.
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;

    }//end getStatus()


    /**
     * Définit le statut du commentaire.
     *
     * @param string $status Le statut du commentaire.
     *
     * @return void
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;

    }//end setStatus()


    /**
     * Obtient l'ID du post associé.
     *
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;

    }//end getPostId()


    /**
     * Définit l'ID du post associé.
     *
     * @param int $postId L'ID du post.
     *
     * @return void
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;

    }//end setPostId()


}//end class
