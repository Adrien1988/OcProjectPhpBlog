<?php

namespace App\Controllers;

use DateTime;
use App\Models\Post;
use Models\PostsRepository;
use App\Controllers\BaseController;
use Models\CommentsRepository;
use Models\UsersRepository;
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
     * @param int                $postId             L'identifiant de l'article
     *                                               à afficher.
     * @param PostsRepository    $postsRepository    Le repository pour accéder
     *                                               aux posts.
     * @param CommentsRepository $commentsRepository Le repository pour accéder aux comments.
     * @param UsersRepository    $usersRepository    Le repository pour accéder aux auteurs.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     *
     * @throws Exception Si l'article n'est pas trouvé.
     */
    public function detailPost(int $postId, PostsRepository $postsRepository, CommentsRepository $commentsRepository, UsersRepository $usersRepository): Response
    {
        try {
            $post = $this->fetchPostOrFail($postId, $postsRepository);

            $author = $usersRepository->findById($post->getAuthor());
            if ($author === null) {
                throw new Exception('Auteur introuvable.', 404);
            }

            $comments = $commentsRepository->findValidatedCommentsByPostId($postId);

            return $this->render(
                'posts/detail.html.twig',
                [
                    'post' => $post,
                    'author' => $author,
                    'comments' => $comments,
                    'csrf_token' => $this->generateCsrfToken('comment_form'),
                ]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

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

        try {
            $authorId = $this->getSessionValue('user_id');
            if ($authorId === null) {
                return $this->redirect('/login');
            }

            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('create_post_form', $request);

                $post = $this->buildPost($request, (int) $authorId);

                $validationErrors = $this->validateEntity($post);
                if (empty($validationErrors) === false) {
                    return $this->renderFormWithErrors(
                        'posts/create.html.twig',
                        $validationErrors,
                        ['post' => $post],
                        $this->generateCsrfToken('create_post_form')
                    );
                }

                $postsRepository->createPost($post);
                return $this->redirect('/posts');
            }

            return $this->render(
                'posts/create.html.twig',
                [
                    'csrf_token' => $this->generateCsrfToken('create_post_form'),
                ]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

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

        try {
            $currentUser = (int) $this->getSessionValue('user_id');
            $post        = $this->fetchPostOrFail($postId, $postsRepository);

            if ($post->getAuthor() !== $currentUser) {
                throw new Exception('Vous n\'êtes pas autorisé à modifier cet article.', 403);
            }

            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('edit_post_form', $request);

                $this->updatePostFromRequest($request, $post);
                $postsRepository->updatePost($post);

                if (is_numeric($postId) === false || (int) $postId <= 0) {
                    throw new Exception('L\'ID du poste est invalide : '.$postId, 400);
                }

                $url = '/posts/'.(int) $postId;
                return $this->redirect($url);
            }

            return $this->render(
                'posts/edit.html.twig',
                [
                    'post' => $post,
                    'csrf_token' => $this->generateCsrfToken('edit_post_form'),
                ]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

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
        try {
            $this->isCsrfTokenValidOrFail('delete_post', $request);
            $this->fetchPostOrFail($postId, $postsRepository);

            $postsRepository->deletePost($postId);

            return $this->redirect('/posts');
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end deletePost()


    /**
     * Construit un article à partir des données de la requête.
     *
     * @param Request $request  La requête HTTP.
     * @param int     $authorId L'identifiant de l'auteur.
     *
     * @return Post L'article créé.
     */
    private function buildPost(Request $request, int $authorId): Post
    {
        return new Post(
            [
                'postId' => null,
                'title' => $this->cleanInput($request->request->get('title')),
                'chapo' => $this->cleanInput($request->request->get('chapo')),
                'content' => $this->cleanInput($request->request->get('content')),
                'author' => $authorId,
                'createdAt' => (new DateTime())->format('Y-m-d H:i:s'),
            ]
        );

    }//end buildPost()


    /**
     * Met à jour un article avec les données de la requête.
     *
     * @param Request $request La requête HTTP.
     * @param Post    $post    L'article à mettre à jour.
     *
     * @return void
     */
    private function updatePostFromRequest(Request $request, Post $post): void
    {
        $post->setTitle($this->cleanInput($request->request->get('title')));
        $post->setChapo($this->cleanInput($request->request->get('chapo')));
        $post->setContent($this->cleanInput($request->request->get('content')));
        $post->setUpdatedAt(new DateTime());

    }//end updatePostFromRequest()


}//end class
