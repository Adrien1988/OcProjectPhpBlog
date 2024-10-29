<?php

namespace App\Controllers;

use DateTime;
use App\Models\Post;
use Models\PostsRepository;
use App\Controllers\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Contrôleur pour la gestion des posts.
 */
class PostController extends BaseController
{


    /**
     * Affiche la liste des articles de blog.
     *
     * @param PostsRepository $postsRepository Le repository pour accéder aux posts.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     */
    public function listPosts(PostsRepository $postsRepository): Response
    {
        // Récupère tous les posts via le repository.
        $posts = $postsRepository->findAll();

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
     * @param int             $postId          L'identifiant de l'article à
     *                                         afficher.
     * @param PostsRepository $postsRepository Le repository pour accéder aux posts.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     *
     * @throws Exception Si l'article n'est pas trouvé.
     */
    public function detailPost(int $postId, PostsRepository $postsRepository): Response
    {
        $post = $postsRepository->findById($postId);

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
     * @param Request         $request         L'objet de la requête HTTP contenant les données du formulaire.
     * @param PostsRepository $postsRepository Le repository pour accéder aux posts.
     *
     * @return Response La réponse HTTP avec le contenu rendu ou un message de succès.
     */
    public function createPost(Request $request, PostsRepository $postsRepository): Response
    {

         // Vérifier si l'utilisateur est connecté.
         $authorId = $this->sessionService->get('user_id');
        if ($authorId === null) {
            // Rediriger vers la page de connexion.
            return new Response('', 302, ['Location' => '/login']);
        }

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

            // Récupérer l'ID de l'auteur depuis la session.
            $authorId = $this->sessionService->get('user_id');

            // Assurer que $authorId est un entier.
            $authorId = (int) $authorId;

            // Crée un nouvel objet Post avec les données nettoyées.
            $postData = [
                'postId' => null,
                'title' => $title,
                'chapo' => $chapo,
                'content' => $postContent,
                'author' => $authorId,
                'createdAt' => new DateTime(),
                'updatedAt' => null,
            ];

            $post = new Post($postData, $this->validator);

            try {
                // Enregistre le post en base de données via le repository.
                $postsRepository->createPost($post);

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
     * @param Request         $request         La requête HTTP contenant les données du formulaire.
     * @param int             $postId          L'identifiant de l'article à modifier.
     * @param PostsRepository $postsRepository Le repository pour accéder aux posts.
     *
     * @return Response La réponse HTTP contenant soit le formulaire, soit une redirection après mise à jour.
     *
     * @throws Exception Si l'article n'est pas trouvé ou si une erreur survient lors de la mise à jour.
     */
    public function editPost(Request $request, int $postId, PostsRepository $postsRepository): Response
    {

        // Vérifier si l'utilisateur est connecté.
        $currentUser = $this->sessionService->get('user_id');
        if ($currentUser === null) {
            // Rediriger vers la page de connexion.
            return new Response('', 302, ['Location' => '/login']);
        }

        // Récupérer l'utilisateur connecté.
        $currentUser = $this->sessionService->get('user_id');

        $post = $postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        // Vérifier si l'utilisateur est l'auteur du post.
        if ($post->getAuthor() !== (int) $currentUser) {
            return new Response('Vous n\'êtes pas autorisé à modifier ce post.', 403);
        }

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
                $postsRepository->updatePost($post);
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
     * @param Request         $request         La requête HTTP contenant les données, y compris le token CSRF.
     * @param int             $postId          L'identifiant de l'article à supprimer.
     * @param PostsRepository $postsRepository Le repository pour accéder aux posts.
     *
     * @return Response La réponse HTTP avec redirection ou message d'erreur.
     *
     * @throws Exception Si l'article n'est pas trouvé ou si une erreur survient lors de la suppression.
     */
    public function deletePost(Request $request, int $postId, PostsRepository $postsRepository): Response
    {
        // Vérifier le token CSRF avant d'effectuer la suppression.
        $submittedToken = $request->request->get('_csrf_token');
        if ($this->isCsrfTokenValid('delete_post', $submittedToken) === false) {
            return new Response('Invalid CSRF token.', 403);
        }

        $post = $postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        try {
            // Supprimer l'article via le repository.
            $postsRepository->deletePost($postId);

            // Rediriger vers la page de listing des posts après suppression.
            return new Response('', 302, ['Location' => '/posts']);
        } catch (Exception $e) {
            return new Response('Erreur lors de la suppression du post : '.$e->getMessage(), 500);
        }

    }//end deletePost()


}//end class
