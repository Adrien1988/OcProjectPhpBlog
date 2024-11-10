<?php

namespace App\Controllers;

use DateTime;
use App\Models\User;
use App\Models\Comment;
use Models\PostsRepository;
use Models\UsersRepository;
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
        // Récupérer le post pour vérifier son existence.
        $post = $postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        // Vérifier le token CSRF.
        $submittedToken = $request->request->get('_csrf_token');
        if ($this->isCsrfTokenValid('comment_form', $submittedToken) === false) {
            return new Response('Token CSRF invalide.', 403);
        }

        $content  = $this->cleanInput($request->request->get('content'));
        $authorId = $this->getSessionValue('user_id');

        if ($authorId === null) {
            // Si l'utilisateur n'est pas connecté, rediriger ou afficher un message.
            return new Response('Vous devez être connecté pour ajouter un commentaire.', 403);
        }

        $comment = new Comment(
            commentId: null,
            content: $content,
            createdAt: new DateTime(),
            isValidated: false,
            postId: $postId,
            author: (int) $authorId
        );

        // Valider le commentaire.
        $violations = $this->validator->validate($comment);

        if (count($violations) > 0) {
            // Gérer les erreurs de validation.
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            // Récupérer les commentaires validés pour le post.
            $comments = $commentsRepository->findValidatedCommentsByPostId($postId);

            // Renvoyer la vue avec les erreurs.
            return $this->render(
                'posts/detail.html.twig',
                [
                    'post' => $post,
                    'comments' => $comments,
                    'errors' => $errors,
                    'csrf_token' => $this->generateCsrfToken('comment_form'),
                ]
            );
        }//end if

        // Enregistrer le commentaire dans la base de données.
        try {
            $commentsRepository->createComment($comment);
            // Rediriger vers la page du post après succès.
            return new Response('', 302, ['Location' => '/posts/'.$postId]);
        } catch (Exception $e) {
            return new Response('Erreur lors de la création du commentaire : '.$e->getMessage(), 500);
        }

    }//end createComment()


    /**
     * Valide un commentaire (pour les administrateurs).
     *
     * @param int                $commentId          L'ID du commentaire à
     *                                               valider.
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     *
     * @return Response
     */
    public function validateComment(int $commentId, CommentsRepository $commentsRepository): Response
    {
        // Vérifier que l'utilisateur est un administrateur.
        $userRole = $this->getSessionValue('user_role');

        if ($userRole !== 'admin') {
            return new Response('Accès interdit', 403);
        }

        // Vérifier si le commentaire existe.
        $comment = $commentsRepository->findById($commentId);
        if ($comment === null) {
            return new Response('Commentaire introuvable.', 404);
        }

        // Mettre à jour le statut du commentaire.
        try {
            $commentsRepository->updateCommentStatus($commentId, true);
            // Rediriger vers la liste des commentaires en attente.
            return new Response('', 302, ['Location' => '/admin/comments/pending']);
        } catch (Exception $e) {
            return new Response('Erreur lors de la validation du commentaire : '.$e->getMessage(), 500);
        }

        // Rediriger vers la liste des commentaires en attente.
        return new Response('', 302, ['Location' => '/admin/comments/pending']);

    }//end validateComment()


    /**
     * Invalide (supprime) un commentaire et notifie l'utilisateur.
     *
     * @param Request            $request            La requête
     *                                               HTTP.
     * @param int                $commentId          L'ID du commentaire à
     *                                               invalider.
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     * @param UsersRepository    $usersRepository    Le repository des utilisateurs.
     *
     * @return Response
     */
    public function invalidateComment(
        Request $request,
        int $commentId,
        CommentsRepository $commentsRepository,
        UsersRepository $usersRepository
    ): Response {
        // Vérifier que l'utilisateur est un administrateur.
        $userRole = $this->getSessionValue('user_role');

        if ($userRole !== 'admin') {
            return new Response('Accès interdit', 403);
        }

        // Vérifier si le commentaire existe.
        $comment = $commentsRepository->findById($commentId);
        if ($comment === null) {
            return new Response('Commentaire introuvable.', 404);
        }

        // Récupérer les informations de l'auteur du commentaire.
        $authorId = $comment->getAuthor();
        $user     = $usersRepository->findById($authorId);

        $reason = $this->cleanInput($request->request->get('reason', 'Non conforme aux règles du blog'));

        if ($user === null) {
            // Si l'utilisateur n'existe pas, continuer sans notification.
            return new Response("Utilisateur introuvable pour le commentaire ID: $commentId");
        }

        // Envoyer une notification à l'utilisateur.
        $this->notifyUserCommentInvalidated($user, $comment, $reason);

        // Supprimer le commentaire.
        try {
            $commentsRepository->deleteComment($commentId);

            // Rediriger vers la liste des commentaires en attente.
            return new Response('', 302, ['Location' => '/admin/comments/pending']);
        } catch (Exception $e) {
            // Enregistrer l'erreur.
            return new Response('Erreur lors de l\'invalidation du commentaire : '.$e->getMessage(), 500);
        }

    }//end invalidateComment()


    /**
     * Notifie l'utilisateur que son commentaire a été invalidé.
     *
     * @param User    $user    L'utilisateur à notifier.
     * @param Comment $comment Le commentaire invalidé.
     * @param string  $reason  La raison de l'invalidation.
     *
     * @return void
     */
    private function notifyUserCommentInvalidated(User $user, Comment $comment, string $reason): void
    {
        $subject = 'Votre commentaire n\'a pas été accepté';
        $message = $this->renderTemplate(
            'emails/comment_invalidated.html.twig',
            [
                'user' => $user,
                'comment' => $comment,
                'reason' => $reason,
            ]
        );

        // Envoyer l'email à l'utilisateur.
        $this->emailService->sendEmail($user->getEmail(), $subject, $message);

    }//end notifyUserCommentInvalidated()


}//end class
