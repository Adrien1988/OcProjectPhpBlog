<?php

namespace App\Controllers;

use Twig\Environment;
use Models\PostsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur pour la gestion des posts.
 */
class PostController
{

    /**
     * Instance de l'environnement Twig pour le rendu des templates.
     *
     * @var Environment
     */
    private $twig;

    /**
     * Le repository des posts pour interagir avec la base de données des articles.
     *
     * @var PostsRepository
     */
    private $postsRepository;


    /**
     * Constructeur de la classe BlogController.
     *
     * @param Environment     $twig            Instance de l'environnement Twig pour le rendu des templates.
     * @param PostsRepository $postsRepository Le repository des articles de blog pour récupérer et manipuler les posts.
     */
    public function __construct(Environment $twig, PostsRepository $postsRepository)
    {
        $this->twig            = $twig;
        $this->postsRepository = $postsRepository;

    }//end __construct()


    /**
     * Affiche la liste des articles de blog.
     *
     * @param Request $request La requête HTTP courante.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     */
    public function listPosts(Request $request): Response
    {
        // Récupère tous les posts via le repository.
        $posts = $this->postsRepository->findAll();

        $content = $this->twig->render(
            'posts/list.html.twig',
            [
                'posts' => $posts,
            ]
        );

        return new Response($content);

    }//end listPosts()


}//end class
