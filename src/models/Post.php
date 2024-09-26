<?php

namespace App\Models;

use App\Models\Traits\IdTrait;
use App\Models\Traits\TimestampableTrait;
use App\Models\Traits\AuthTrait;
use DateTime;

/**
 * Classe Post représentant une entité pour les articles dans la base de données.
 */
class Post
{
    use IdTrait, TimestampableTrait, AuthTrait;

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
     * Constructeur de la classe Post.
     *
     * @param int       $id        L'identifiant unique du post.
     * @param string    $title     Le titre du post.
     * @param string    $chapo     Le chapo (introduction) du post.
     * @param string    $content   Le contenu principal du post.
     * @param int       $author    L'identifiant de l'auteur.
     * @param DateTime  $createdAt La date de création du
     *                             post.
     * @param ?DateTime $updatedAt La date de mise à jour du post (peut être null).
     */
    public function __construct(
        int $id,
        string $title,
        string $chapo,
        string $content,
        int $author,
        DateTime $createdAt,
        ?DateTime $updatedAt=null
    ) {
        $this->setId($id);
        $this->title   = $title;
        $this->chapo   = $chapo;
        $this->content = $content;
        $this->setAuthor($author);
        $this->setCreatedAt($createdAt);
        $this->setUpdatedAt($updatedAt);

    }//end __construct()


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
     * @param string $title the title of the post.
     *
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
     * @param string $chapo the introductory paragraph of the post.
     *
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
     * @param string $content the main content of the post.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


}//end class
