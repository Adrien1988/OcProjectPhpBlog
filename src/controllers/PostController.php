<?php

namespace App\Controllers;


use Models\PostsRepository;
use App\Controllers\BaseController;
use Models\CommentsRepository;
use Models\UsersRepository;
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


}//end class
