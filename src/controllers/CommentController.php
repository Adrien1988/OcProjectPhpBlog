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
     * Affiche la liste des commentaires en attente de validation.
     *
     * @param CommentsRepository $commentsRepository Le repository des commentaires.
     *
     * @return Response
     */
    public function listPendingComments(CommentsRepository $commentsRepository): Response
    {
        return $this->listComments('pending', 'admin/comments_pending.html.twig', $commentsRepository);

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
        try {
            $this->ensureAdminAccess();
            $this->fetchCommentOrFail($commentId, $commentsRepository);

            $commentsRepository->updateCommentStatus($commentId, 'validated');
            return $this->listPendingComments($commentsRepository);
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
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

        // Marquer le commentaire comme invalidé (non validé) dans la base de données.
        try {
            $this->ensureAdminAccess();
            $comment = $this->fetchCommentOrFail($commentId, $commentsRepository);
            $user    = $this->fetchUserOrFail($comment->getAuthor(), $usersRepository);

            $reason = $this->cleanInput($request->request->get('reason', 'Non conforme aux règles du blog'));

            // Envoyer une notification à l'utilisateur.
            $this->notifyUserCommentInvalidated($user, $comment, $reason);

            $commentsRepository->updateCommentStatus($commentId, 'rejected');
            return $this->listPendingComments($commentsRepository);
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end invalidateComment()


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
        try {
            $this->ensureAdminAccess();
            $comment = $this->fetchCommentOrFail($commentId, $commentsRepository);

            if ($comment->getStatus() === 'validated') {
                throw new Exception('Commentaire déjà validé.', 400);
            }

            $commentsRepository->deleteComment($commentId);
            return $this->redirect('/admin/invalidated');
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end deleteInvalidatedComment()


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
        return $this->listComments('validated', 'admin/comments_validated.html.twig', $commentsRepository);

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
        return $this->listComments('rejected', 'admin/comments_invalidated.html.twig', $commentsRepository);

    }//end listInvalidatedComments()


    /**
     * Récupère un commentaire ou lève une exception s'il est introuvable.
     *
     * @param int                $commentId          L'identifiant du commentaire à
     *                                               récupérer.
     * @param CommentsRepository $commentsRepository Le repository utilisé pour accéder aux
     *                                               commentaires.
     *
     * @return object Le commentaire trouvé.
     *
     * @throws Exception Si le commentaire n'est pas trouvé.
     */
    private function fetchCommentOrFail(int $commentId, CommentsRepository $commentsRepository): object
    {
        $comment = $commentsRepository->findById($commentId);
        if ($comment === null) {
            throw new Exception('Commentaire introuvable.', 404);
        }

        return $comment;

    }//end fetchCommentOrFail()


    /**
     * Récupère un utilisateur ou lève une exception s'il est introuvable.
     *
     * @param int             $userId          L'identifiant de l'utilisateur à
     *                                         récupérer.
     * @param UsersRepository $usersRepository Le repository utilisé pour accéder aux
     *                                         utilisateurs.
     *
     * @return object L'utilisateur trouvé.
     *
     * @throws Exception Si l'utilisateur n'est pas trouvé.
     */
    private function fetchUserOrFail(int $userId, UsersRepository $usersRepository): object
    {
        $user = $usersRepository->findById($userId);
        if ($user === null) {
            throw new Exception('Utilisateur introuvable.', 404);
        }

        return $user;

    }//end fetchUserOrFail()


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


    /**
     * Affiche les commentaires par statut.
     *
     * @param string             $status             Le statut des commentaires à récupérer (e.g., "pending", "validated").
     * @param string             $template           Le chemin du template Twig à utiliser pour l'affichage.
     * @param CommentsRepository $commentsRepository Le repository utilisé pour accéder aux commentaires.
     *
     * @return Response La réponse HTTP contenant les commentaires rendus dans la vue.
     */
    private function listComments(string $status, string $template, CommentsRepository $commentsRepository): Response
    {
        try {
            $this->ensureAdminAccess();
            $comments = $commentsRepository->findCommentsByStatus($status);
            return $this->render(
                $template,
                [
                    'comments' => $comments,
                    'csrf_token' => $this->generateCsrfToken('comment_action'),
                ]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end listComments()


    /**
     * Vérifie si l'utilisateur est administrateur.
     *
     * @return bool True si l'utilisateur a le rôle "Admin", sinon False.
     */
    private function isAdmin(): bool
    {
        return $this->getSessionValue('user_role') === 'Admin';

    }//end isAdmin()


    /**
     * Assure que l'utilisateur est administrateur, sinon lève une exception.
     *
     * @throws Exception Si l'utilisateur n'est pas administrateur.
     *
     * @return void
     */
    private function ensureAdminAccess(): void
    {
        if ($this->isAdmin() === false) {
            throw new Exception('Accès interdit', 403);
        }

    }//end ensureAdminAccess()


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


}//end class
