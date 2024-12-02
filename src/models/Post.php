<?php

namespace App\Models;

use DateTime;
use App\Models\Traits\IdTrait;
use App\Models\Traits\AuthTrait;
use App\Models\Traits\TimestampableTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Classe Post représentant une entité pour les articles dans la base de données.
 */
class Post
{
    use IdTrait, TimestampableTrait, AuthTrait;

    /**
     * Le titre de l'article.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le titre ne peut pas être vide.")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "Le titre doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le titre ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $title;

    /**
     * Le chapô de l'article.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le chapô ne peut pas être vide.")]
    #[Assert\Length(
        min: 10,
        max: 500,
        minMessage: "Le chapô doit comporter au moins {{ limit }} caractères.",
        maxMessage: "Le chapô ne peut pas dépasser {{ limit }} caractères."
    )]
    private string $chapo;

    /**
     * Le contenu principal de l'article.
     *
     * @var string
     */
    #[Assert\NotBlank(message: "Le contenu ne peut pas être vide.")]
    #[Assert\Length(
        min: 20,
        minMessage: "Le contenu doit comporter au moins {{ limit }} caractères."
    )]
    private string $content;

    /**
     * Le prénom de l'auteur.
     *
     * @var string|null
     */
    private ?string $authorFirstName;

    /**
     * Le nom de famille de l'auteur.
     *
     * @var string|null
     */
    private ?string $authorLastName;


    /**
     * Constructeur de la classe Post.
     *
     * @param array $postData Tableau contenant les données de l'article :
     *                        - 'postId' : int|null, l'ID de l'article.
     *                        - 'title' : string, le titre de l'article.
     *                        - 'chapo' : string, le chapô de l'article.
     *                        - 'content' : string, le contenu de l'article.
     *                        - 'createdAt' : string, la date de création de l'article.
     *                        - 'author' : int, l'ID de l'auteur de l'article.
     *                        - 'updatedAt' : string|null, la date de mise à jour de l'article.
     */
    public function __construct(array $postData)
    {
        $this->setId(($postData['postId'] ?? null));
        $this->setTitle($postData['title']);
        $this->setChapo($postData['chapo']);
        $this->setContent($postData['content']);
        $this->setCreatedAt(new DateTime($postData['createdAt']));
        $this->setAuthor($postData['author']);

        // Initialisation des propriétés authorFirstName et authorLastName.
        $this->authorFirstName = ($postData['authorFirstName'] ?? null);
        $this->authorLastName  = ($postData['authorLastName'] ?? null);

        if (isset($postData['updatedAt']) === true) {
            $this->setUpdatedAt(new DateTime($postData['updatedAt']));
        }

    }//end __construct()


    /**
     * Obtient le titre de l'article.
     *
     * @return string Le titre de l'article.
     */
    public function getTitle(): string
    {
        return $this->title;

    }//end getTitle()


    /**
     * Définit le titre de l'article.
     *
     * @param string $title Le titre de l'article.
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;

    }//end setTitle()


    /**
     * Obtient le chapô de l'article.
     *
     * @return string Le chapô de l'article.
     */
    public function getChapo(): string
    {
        return $this->chapo;

    }//end getChapo()


    /**
     * Définit le chapô de l'article.
     *
     * @param string $chapo Le chapô de l'article.
     *
     * @return void
     */
    public function setChapo(string $chapo): void
    {
        $this->chapo = $chapo;

    }//end setChapo()


    /**
     * Obtient le contenu principal de l'article.
     *
     * @return string Le contenu principal de l'article.
     */
    public function getContent(): string
    {
        return $this->content;

    }//end getContent()


    /**
     * Définit le contenu principal de l'article.
     *
     * @param string $content Le contenu principal de l'article.
     *
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


    /**
     * Obtient le prénom de l'auteur.
     *
     * @return string|null Le prénom de l'auteur.
     */
    public function getAuthorFirstName(): ?string
    {
        return $this->authorFirstName;

    }//end getAuthorFirstName()


    /**
     * Obtient le nom de famille de l'auteur.
     *
     * @return string|null Le nom de famille de l'auteur.
     */
    public function getAuthorLastName(): ?string
    {
        return $this->authorLastName;

    }//end getAuthorLastName()


}//end class
