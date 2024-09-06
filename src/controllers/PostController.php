<?php

namespace App\Controllers;

use App\Models\Post;
use Twig\Environment;
use Models\PostsRepository;
use App\Services\SecurityService;
use App\Services\CsrfService;
use PHPMailer\PHPMailer\Exception;
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
     * Le service de sécurité pour la protection et le nettoyage des entrées utilisateur.
     *
     * @var SecurityService
     */
    private $securityService;

    /**
     * Service pour la gestion des tokens CSRF.
     *
     * @var CsrfService
     */
    private $csrfService;


    /**
     * Constructeur de la classe PostController.
     * Initialise les dépendances pour le rendu des templates, la gestion des articles et la sécurité.
     *
     * @param Environment     $twig            Instance de l'environnement Twig pour le rendu des templates.
     * @param PostsRepository $postsRepository Le repository des articles de blog pour récupérer et manipuler les posts.
     * @param SecurityService $securityService Le service de sécurité pour la protection et le nettoyage des entrées utilisateur.
     * @param CsrfService     $csrfService     Service pour la gestion des tokens CSRF.
     */
    public function __construct(Environment $twig, PostsRepository $postsRepository, SecurityService $securityService, CsrfService $csrfService)
    {
        $this->twig            = $twig;
        $this->postsRepository = $postsRepository;
        $this->securityService = $securityService;
        $this->csrfService     = $csrfService;

    }//end __construct()


    /**
     * Affiche la liste des articles de blog.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     */
    public function listPosts(): Response
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


    /**
     * Affiche le formulaire de création d'un nouveau post et traite la soumission du formulaire.
     *
     * @param Request $request L'objet de la requête HTTP contenant les données
     *                         du formulaire.
     *
     * @return Response La réponse HTTP avec le contenu rendu ou un message de succès.
     */
    public function createPost(Request $request): Response
    {

        if ($request->isMethod('POST') === true) {
            // Vérifier le token CSRF.
            $submittedToken = $request->request->get('_csrf_token');
            if ($this->csrfService->isTokenValid('create_post_form', $submittedToken) === false) {
                return new Response('Invalid CSRF token.', 403);
            }

            // Nettoyage des entrées utilisateur avec SecurityService.
            $title       = $this->securityService->cleanInput($request->request->get('title'));
            $chapo       = $this->securityService->cleanInput($request->request->get('chapo'));
            $postContent = $this->securityService->cleanInput($request->request->get('content'));

            // Crée un nouvel objet Post avec les données nettoyées.
            $post = new Post();
            $post->setTitle($title);
            $post->setChapo($chapo);
            $post->setContent($postContent);
            $post->setAuthor(1);
            $post->setCreatedAt(new \DateTime());
            $post->setUpdatedAt(new \DateTime());

            try {
                // Enregistre le post en base de données via le repository.
                $this->postsRepository->createPost($post);

                // Rediriger vers la page de listing des posts après la création.
                return new Response('', 302, ['Location' => '/posts/list']);
            } catch (Exception $e) {
                return new Response('Erreur lors de la création du post : '.$e->getMessage(), 500);
            }
        }//end if

        $content = $this->twig->render('posts/create.html.twig');
        return new Response($content);

    }//end createPost()


}//end class
