<?php

namespace App\Controllers;

use DateTime;
use App\Models\Post;
use Twig\Environment;
use Models\PostsRepository;
use App\Services\CsrfService;
use App\Services\SecurityService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

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
     * Affiche le détail d'un article de blog.
     *
     * @param int $postId L'identifiant de l'article à afficher.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     *
     * @throws Exception Si l'article n'est pas trouvé.
     */
    public function detailPost(int $postId): Response
    {
        $post = $this->postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        $content = $this->twig->render(
            'posts/detail.html.twig',
            [
                'post' => $post,
            ]
        );

        return new Response($content);

    }//end detailPost()


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
            $post = new Post(
                postId: 0,
                title: $title,
                chapo: $chapo,
                content: $postContent,
                author: 1,
                createdAt: new DateTime(),
                updatedAt: new DateTime()
            );
            $post->setTitle($title);
            $post->setChapo($chapo);
            $post->setContent($postContent);
            $post->setAuthor(1);
            $post->setCreatedAt(new DateTime());
            $post->setUpdatedAt(new DateTime());

            try {
                // Enregistre le post en base de données via le repository.
                $this->postsRepository->createPost($post);

                // Rediriger vers la page de listing des posts après la création.
                return new Response('', 302, ['Location' => '/posts']);
            } catch (Exception $e) {
                return new Response('Erreur lors de la création du post : '.$e->getMessage(), 500);
            }
        }//end if

        $csrfToken = $this->csrfService->generateToken('create_post_form');
        $content   = $this->twig->render('posts/create.html.twig', ['csrf_token' => $csrfToken]);
        return new Response($content);

    }//end createPost()


}//end class
