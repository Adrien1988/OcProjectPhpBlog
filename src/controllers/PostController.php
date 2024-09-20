<?php

namespace App\Controllers;

use DateTime;
use App\Models\Post;
use Models\PostsRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\SecurityService;
use App\Controllers\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Twig\Environment;

/**
 * Contrôleur pour la gestion des posts.
 */
class PostController extends BaseController
{

    /**
     * Le repository des articles de blog.
     *
     * @var PostsRepository
     */
    private $postsRepository;


    /**
     * Constructeur de la classe PostController.
     *
     * @param PostsRepository $postsRepository Le repository des articles de blog.
     * @param Environment     $twig            L'environnement Twig pour le rendu des templates.
     * @param SecurityService $securityService Le service de sécurité pour la protection contre les attaques XSS.
     * @param EnvService      $envService      Le service de gestion des variables d'environnement.
     * @param CsrfService     $csrfService     Le service de gestion des tokens CSRF.
     */
    public function __construct(PostsRepository $postsRepository, Environment $twig, SecurityService $securityService, EnvService $envService, CsrfService $csrfService)
    {
        // Appel du constructeur du BaseController avec toutes les dépendances nécessaires.
        parent::__construct($twig, $securityService, $envService, $csrfService);

        // Injection du PostsRepository spécifique au PostController.
        $this->postsRepository = $postsRepository;

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

        return $this->render(
            'posts/list.html.twig',
            [
                'posts' => $posts,
            ]
        );

    }//end listPosts()


    /**
     * Affiche le détail d'un article de blog.
     * Cette méthode récupère un article à partir de son identifiant et rend une vue avec ses détails.
     *
     * @param Request $request La requête HTTP courante.
     * @param int     $postId  L'identifiant de l'article à afficher.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     *
     * @throws Exception Si l'article n'est pas trouvé.
     */
    public function detailPost(Request $request, int $postId): Response
    {
        $post = $this->postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        return $this->render(
            'posts/detail.html.twig',
            [
                'post' => $post,
            ]
        );

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
            if ($this->isCsrfTokenValid('create_post_form', $submittedToken) === false) {
                return new Response('Invalid CSRF token.', 403);
            }

            // Nettoyage des entrées utilisateur avec SecurityService.
            $title       = $this->cleanInput($request->request->get('title'));
            $chapo       = $this->cleanInput($request->request->get('chapo'));
            $postContent = $this->cleanInput($request->request->get('content'));

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

        $csrfToken = $this->generateCsrfToken('create_post_form');
        return $this->render('posts/create.html.twig', ['csrf_token' => $csrfToken]);

    }//end createPost()


    /**
     * Modifie un article de blog.
     * Cette méthode affiche le formulaire de modification d'un post, et si une requête POST est envoyée,
     * elle met à jour l'article avec les nouvelles informations.
     *
     * @param Request $request La requête HTTP contenant les données du formulaire.
     * @param int     $postId  L'identifiant de l'article à modifier.
     *
     * @return Response La réponse HTTP, contenant soit le formulaire, soit une redirection après mise à jour.
     *
     * @throws Exception Si l'article ou l'utilisateur n'est pas trouvé ou si une erreur survient lors de la mise à jour.
     */
    public function editPost(Request $request, int $postId): Response
    {
        $post = $this->postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        // // Récupérer l'utilisateur authentifié (en supposant que SecurityService a une méthode getUser())
        // $currentUser = $this->securityService->getUser(); // Supposons que cette méthode retourne l'utilisateur connecté
        // if ($currentUser === null) {
        // throw new Exception('Utilisateur non authentifié');
        // }
        // Si la requête est en POST, on traite le formulaire de modification.
        if ($request->isMethod('POST') === true) {
            $submittedToken = $request->request->get('_csrf_token');
            if ($this->isCsrfTokenValid('edit_post_form', $submittedToken) === false) {
                return new Response('Invalid CSRF token.', 403);
            }

            $title       = $this->cleanInput($request->request->get('title'));
            $chapo       = $this->cleanInput($request->request->get('chapo'));
            $postContent = $this->cleanInput($request->request->get('content'));

            // Mettre à jour les informations du post.
            $post->setTitle($title);
            $post->setChapo($chapo);
            // $post->setAuthor($currentUser->getId());
            $post->setContent($postContent);
            $post->setUpdatedAt(new DateTime());

            try {
                // Sauvegarder les modifications via le repository.
                $this->postsRepository->updatePost($post);
                return new Response('', 302, ['Location' => '/posts/'.$postId]);
            } catch (Exception $e) {
                return new Response('Erreur lors de la modification du post : '.$e->getMessage(), 500);
            }
        }//end if

        // Sinon, on affiche la page avec le formulaire pré-rempli.
        $csrfToken = $this->generateCsrfToken('edit_post_form');
        return $this->render('posts/edit.html.twig', ['post' => $post, 'csrf_token' => $csrfToken]);

    }//end editPost()


    /**
     * Supprime un article de blog.
     * Cette méthode vérifie le jeton CSRF, supprime l'article identifié par son identifiant,
     * puis redirige vers la page de listing des posts.
     *
     * @param Request $request La requête HTTP contenant les données, y compris le token CSRF.
     * @param int     $postId  L'identifiant de l'article à supprimer.
     *
     * @return Response La réponse HTTP, avec redirection ou message d'erreur.
     *
     * @throws Exception Si l'article n'est pas trouvé ou si une erreur survient lors de la suppression.
     */
    public function deletePost(Request $request, int $postId): Response
    {
        // Vérifier le token CSRF avant d'effectuer la suppression.
        $submittedToken = $request->request->get('_csrf_token');
        if ($this->isCsrfTokenValid('delete_post', $submittedToken) === false) {
            return new Response('Invalid CSRF token.', 403);
        }

        $post = $this->postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        try {
            // Supprimer l'article via le repository.
            $this->postsRepository->deletePost($postId);

            // Rediriger vers la page de listing des posts après suppression.
            return new Response('', 302, ['Location' => '/posts']);
        } catch (Exception $e) {
            return new Response('Erreur lors de la suppression du post : '.$e->getMessage(), 500);
        }

    }//end deletePost()


}//end class
