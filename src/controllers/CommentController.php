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
        $authorId = $this->sessionService->get('user_id');
        if ($authorId === null) {
            // Si l'utilisateur n'est pas connecté, rediriger ou afficher un message.
            return new Response('', 302, ['Location' => '/login']);
        }

        // Récupérer le post pour vérifier son existence.
        $post = $postsRepository->findById($postId);

        if ($post === null) {
            throw new Exception('Post not found');
        }

        // Vérifier le token CSRF.
        $submittedToken = $request->request->get('_csrf_token');
        if ($this->isCsrfTokenValid('add_comment', $submittedToken) === false) {
            return new Response('Token CSRF invalide.', 403);
        }

        $content = $this->cleanInput($request->request->get('content'));

        $comment = new Comment(
            [
                'commentId' => null,
                'content' => $content,
                'createdAt' => (new DateTime())->format('Y-m-d H:i:s'),
                'postId' => $postId,
                'author' => (int) $authorId,
                'status' => 'pending',
            ]
        );

        // Valider le commentaire.
        $violations = $this->validator->validate($comment);

        if (count($violations) > 0) {
            // Gérer les erreurs de validation.
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            // Renvoyer la vue avec les erreurs.
            return $this->render(
                'posts/detail.html.twig',
                [
                    'post' => $post,
                    'errors' => $errors,
                    'csrf_token' => $this->generateCsrfToken('add_comment'),
                ]
            );
        }//end if

        // Enregistrer le commentaire dans la base de données.
        try {
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
            return new Response('Erreur lors de la création du commentaire : '.$e->getMessage(), 500);
        }

    }//end createComment()


    /**
     * Affiche la liste des commentaires en attente de validation.
     *
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     *
     * @return Response
     */
    public function listPendingComments(CommentsRepository $commentsRepository): Response
    {
        $userRole = $this->getSessionValue('user_role');

        if ($userRole !== 'Admin') {
            return new Response('Accès interdit', 403);
        }

        // Récupérer les commentaires en attente de validation.
        $comments = $commentsRepository->findCommentsByStatus('pending');

        // Rendre la vue avec les commentaires.
        return $this->render(
            'admin/comments_pending.html.twig',
            [
                'comments' => $comments,
                'csrf_token' => $this->generateCsrfToken('comment_action'),
            ]
        );

    }//end listPendingComments()


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
        $userRole = $this->getSessionValue('user_role');
        if ($userRole !== 'Admin') {
            return new Response('Accès interdit', 403);
        }

        $comment = $commentsRepository->findById($commentId);
        if ($comment === null) {
            return new Response('Commentaire introuvable.', 404);
        }

        try {
            $commentsRepository->updateCommentStatus($commentId, 'validated');

            // Récupérer les commentaires en attente pour mettre à jour la page actuelle.
            $comments = $commentsRepository->findCommentsByStatus('pending');

            // Rediriger vers la vue des commentaires validés.
            return $this->render('admin/comments_pending.html.twig', ['comments' => $comments, 'successMessage' => 'Le commentaire a été validé avec succès.', 'csrf_token' => $this->generateCsrfToken('comment_action')]);
        } catch (Exception $e) {
            return new Response('Erreur lors de la validation du commentaire : '.$e->getMessage(), 500);
        }

    }//end validateComment()


    /**
     * Invalide un commentaire en mettant à jour son statut et notifie l'utilisateur.
     *
     * @param Request            $request            La requête HTTP.
     * @param int                $commentId          L'ID du commentaire à invalider.
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

        if ($userRole !== 'Admin') {
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
            return new Response("Utilisateur introuvable pour le commentaire ID: $commentId");
        }

        // Envoyer une notification à l'utilisateur.
        $this->notifyUserCommentInvalidated($user, $comment, $reason);

        // Marquer le commentaire comme invalidé (non validé) dans la base de données.
        try {
            $commentsRepository->updateCommentStatus($commentId, 'rejected');

            // Récupérer les commentaires en attente pour mettre à jour la page actuelle.
            $comments = $commentsRepository->findCommentsByStatus('pending');

            // Rediriger vers la liste des commentaires invalidés.
            return $this->render(
                'admin/comments_pending.html.twig',
                [
                    'comments' => $comments,
                    'successMessage' => 'Le commentaire a été rejeté avec succès.',
                    'csrf_token' => $this->generateCsrfToken('comment_action'),
                ]
            );
        } catch (Exception $e) {
            return new Response('Erreur lors de l\'invalidation du commentaire : '.$e->getMessage(), 500);
        }

    }//end invalidateComment()


    /**
     * Affiche la liste des commentaires validés pour les administrateurs.
     *
     * Cette méthode vérifie si l'utilisateur a le rôle "Admin".
     * Si oui, elle récupère les commentaires ayant le statut "validated"
     * et les affiche dans une vue dédiée.
     *
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     *
     * @return Response La réponse HTTP contenant la vue des commentaires validés ou une erreur 403 si non autorisé.
     */
    public function listValidatedComments(CommentsRepository $commentsRepository): Response
    {
        $userRole = $this->getSessionValue('user_role');
        if ($userRole !== 'Admin') {
            return new Response('Accès interdit', 403);
        }

        // Récupérer les commentaires validés.
        $comments = $commentsRepository->findCommentsByStatus('validated');

        // Afficher la vue des commentaires validés.
        return $this->render(
            'admin/comments_validated.html.twig',
            [
                'comments' => $comments,
                'csrf_token' => $this->generateCsrfToken('comment_action'),
            ]
        );

    }//end listValidatedComments()


    /**
     * Affiche la liste des commentaires invalidés.
     *
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     *
     * @return Response
     */
    public function listInvalidatedComments(CommentsRepository $commentsRepository): Response
    {
        // Vérifier que l'utilisateur est un administrateur.
        $userRole = $this->getSessionValue('user_role');
        if ($userRole !== 'Admin') {
            return new Response('Accès interdit', 403);
        }

        // Récupérer les commentaires invalidés.
        $comments = $commentsRepository->findCommentsByStatus('rejected');

        // Afficher la vue des commentaires invalidés.
        return $this->render(
            'admin/comments_invalidated.html.twig',
            [
                'comments' => $comments,
                'csrf_token' => $this->generateCsrfToken('comment_action'),
            ]
        );

    }//end listInvalidatedComments()


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
            'emails/comments_invalidated.html.twig',
            [
                'user' => $user,
                'comment' => $comment,
                'reason' => $reason,
            ]
        );

        // Envoyer l'email à l'utilisateur.
        $this->emailService->sendEmail($user->getEmail(), $subject, $message);

    }//end notifyUserCommentInvalidated()


    /**
     * Supprime un commentaire invalidé de la base de données.
     *
     * @param int                $commentId          L'ID du commentaire à supprimer.
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     *
     * @return Response
     */
    public function deleteInvalidatedComment(int $commentId, CommentsRepository $commentsRepository): Response
    {
        // Vérifier que l'utilisateur est un administrateur.
        $userRole = $this->getSessionValue('user_role');
        if ($userRole !== 'Admin') {
            return new Response('Accès interdit', 403);
        }

        // Vérifier si le commentaire existe et est invalidé.
        $comment = $commentsRepository->findById($commentId);
        if ($comment === null || $comment->getStatus() === 'validated') {
            return new Response('Commentaire introuvable ou déjà validé.', 404);
        }

        // Supprimer le commentaire.
        try {
            $commentsRepository->deleteComment($commentId);

            // Rediriger vers la liste des commentaires invalidés avec un message de succès.
            return new Response('', 302, ['Location' => '/admin/invalidated']);
        } catch (Exception $e) {
            return new Response('Erreur lors de la suppression du commentaire : '.$e->getMessage(), 500);
        }

    }//end deleteInvalidatedComment()


}//end class
