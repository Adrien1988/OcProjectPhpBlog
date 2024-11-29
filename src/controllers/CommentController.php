<?php

namespace App\Controllers;

use DateTime;
use App\Models\Comment;
use Models\PostsRepository;
use Models\CommentsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

class CommentController extends BaseController
{


    /**
     * Traite la soumission du formulaire pour ajouter un nouveau commentaire.
     *
     * @param Request            $request            La requête
     *                                               HTTP.
     * @param int                $postId             L'ID du post
     *                                               associé.
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     * @param PostsRepository    $postsRepository    Le repository des posts.
     *
     * @return Response
     */
    public function createComment(Request $request, int $postId, CommentsRepository $commentsRepository, PostsRepository $postsRepository): Response
    {
        // Enregistrer le commentaire dans la base de données.
        try {
            $authorId = $this->getSessionValue('user_id');
            if ($authorId === null) {
                // Si l'utilisateur n'est pas connecté, rediriger ou afficher un message.
                return $this->redirect('/login');
            }

            $post = $this->fetchPostOrFail($postId, $postsRepository);
            $this->isCsrfTokenValid('add_comment', $request);

            $comment = $this->buildComment($request, $postId, $authorId);

            $validationErrors = $this->validateEntity($comment);
            if (empty($validationErrors) === false) {
                return $this->renderFormWithErrors(
                    'posts/detail.html.twig',
                    $validationErrors,
                    ['post' => $post],
                    $this->generateCsrfToken('add_comment')
                );
            }

            $commentsRepository->createComment($comment);

            // Renvoyer la vue avec le message de succès.
            return $this->render(
                'posts/detail.html.twig',
                [
                    'post' => $post,
                    'successMessage' => 'Votre commentaire a été créé avec succès et est en attente de validation.',
                    'csrf_token' => $this->generateCsrfToken('add_comment'),
                ]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

    }//end createComment()


    /**
     * Construit un objet Comment à partir des données de la requête.
     *
     * @param Request $request  La requête HTTP contenant les données nécessaires.
     * @param int     $postId   L'identifiant du post associé au commentaire.
     * @param int     $authorId L'identifiant de l'auteur du commentaire.
     *
     * @return Comment Le commentaire construit.
     */
    private function buildComment(Request $request, int $postId, int $authorId): Comment
    {
        return new Comment(
            [
                'commentId' => null,
                'content' => $this->cleanInput($request->request->get('content')),
                'createdAt' => (new DateTime())->format('Y-m-d H:i:s'),
                'postId' => $postId,
                'author' => $authorId,
                'status' => 'pending',
            ]
        );

    }//end buildComment()


}//end class
