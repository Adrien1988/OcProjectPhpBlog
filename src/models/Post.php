<?php

namespace App\Models;

use App\Models\Traits\IdTrait;
use App\Models\Traits\TimestampableTrait;
use App\Models\Traits\AuthTrait;
use DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Classe Post représentant une entité pour les articles dans la base de données.
 */
class Post
{
    use IdTrait, TimestampableTrait, AuthTrait;

    /**
     * Le validateur Symfony.
     *
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

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
     * @param array              $postData  Tableau contenant les données du post.
     * @param ValidatorInterface $validator Le validateur Symfony à injecter.
     */
    public function __construct(array $postData, ValidatorInterface $validator)
    {
        $this->setId(($postData['postId'] ?? null));
        $this->title   = $postData['title'];
        $this->chapo   = $postData['chapo'];
        $this->content = $postData['content'];
        $this->setAuthor($postData['author']);
        $this->setCreatedAt($postData['createdAt']);
        $this->setUpdatedAt(($postData['updatedAt'] ?? null));
        $this->validator = $validator;

    }//end __construct()


    /**
     * Injecte le validateur Symfony dans l'entité Post.
     *
     * @param ValidatorInterface $validator Le validateur Symfony à injecter.
     *
     * @return void
     */
    public function setValidator(ValidatorInterface $validator): void
    {
        $this->validator = $validator;

    }//end setValidator()


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


    /**
     * Valide l'entité Post en utilisant le validateur Symfony.
     *
     * @return ConstraintViolationListInterface|null Retourne la liste des violations ou null s'il n'y en a pas.
     */
    public function validate(): ?ConstraintViolationListInterface
    {
        $constraints = new Assert\Collection(
            [
                'title' => [new Assert\NotBlank(), new Assert\Length(['min' => 5, 'max' => 255])],
                'chapo' => [new Assert\NotBlank(), new Assert\Length(['min' => 10, 'max' => 500])],
                'content' => [new Assert\NotBlank(), new Assert\Length(['min' => 20])],
                'author' => [new Assert\NotBlank(), new Assert\Type('int')],
            ]
        );

        $data = [
            'title'   => $this->getTitle(),
            'chapo'   => $this->getChapo(),
            'content' => $this->getContent(),
            'author'  => $this->getAuthor(),
        ];

        return $this->validator->validate($data, $constraints);

    }//end validate()


}//end class
