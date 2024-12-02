<?php

namespace App\Controllers;

use DateTime;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Models\PostsRepository;
use Models\UsersRepository;
use Models\CommentsRepository;
use App\Controllers\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;


class AdminController extends BaseController
{


    /**
     * Vérifie que l'utilisateur actuel est un administrateur.
     *
     * @throws Exception Si l'utilisateur n'est pas connecté ou n'a pas les privilèges administrateur.
     *
     * @return void
     */
    protected function ensureIsAdmin(): void
    {
        if (strtolower($this->sessionService->get('user_role')) !== 'admin') {
            // Rediriger vers la page de connexion ou afficher une erreur.
            throw new Exception('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 403);
        }

    }//end ensureIsAdmin()


    /**
     * Affiche le tableau de bord de l'administration.
     *
     * @return Response La réponse HTTP contenant la page du tableau de bord.
     */
    public function dashboard(): Response
    {
         // Afficher le tableau de bord.
         return $this->render('admin/dashboard.html.twig');

    }//end dashboard()


    /**
     * Affiche la liste des articles dans la section d'administration.
     *
     * @param PostsRepository $postsRepository Le repository pour accéder aux posts.
     *
     * @return Response La réponse HTTP contenant la liste des articles.
     */
    public function listAdminPosts(PostsRepository $postsRepository): Response
    {
        $this->ensureIsAdmin();

        $posts = $postsRepository->findAll();

        return $this->render(
            'admin/posts_list_admin.html.twig',
            ['posts' => $posts]
        );

    }//end listAdminPosts()


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
            $this->ensureIsAdmin();

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
                        'admin/create_post.html.twig',
                        $validationErrors,
                        ['post' => $post],
                        $this->generateCsrfToken('create_post_form')
                    );
                }

                $postsRepository->createPost($post);
                return $this->redirect('/admin/posts');
            }

            return $this->render(
                'admin/create_post.html.twig',
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
     * @param UsersRepository $usersRepository Le repository des utilisateurs.
     *
     * @return Response La réponse HTTP contenant soit le formulaire, soit une redirection après mise à jour.
     *
     * @throws Exception Si l'article n'est pas trouvé ou si une erreur survient lors de la mise à jour.
     */
    public function editPost(Request $request, int $postId, PostsRepository $postsRepository, UsersRepository $usersRepository): Response
    {

        try {
            $this->ensureIsAdmin();

            $post = $this->fetchPostOrFail($postId, $postsRepository);

            // Récupérer la liste des utilisateurs.
            $users = $usersRepository->findAll();

            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('edit_post_form', $request);

                $this->updatePostFromRequest($request, $post);
                $validationErrors = $this->validateEntity($post);
                if (empty($validationErrors) === false) {
                    return $this->renderFormWithErrors(
                        'admin/edit_post.html.twig',
                        $validationErrors,
                        ['post' => $post, 'users' => $users],
                        $this->generateCsrfToken('edit_post_form')
                    );
                }

                $postsRepository->updatePost($post);

                return $this->redirect('/admin/posts');
            }//end if

            return $this->render(
                'admin/edit_post.html.twig',
                [
                    'post' => $post,
                    'users' => $users,
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
            $this->ensureIsAdmin();

            $this->isCsrfTokenValidOrFail('delete_post', $request);
            $this->fetchPostOrFail($postId, $postsRepository);

            $postsRepository->deletePost($postId);

            return $this->redirect('/admin/posts');
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end deletePost()


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
            $this->ensureIsAdmin();
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
            $this->ensureIsAdmin();
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
            $this->ensureIsAdmin();
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
     * Affiche la liste des utilisateurs.
     *
     * @param UsersRepository $usersRepository Le repository des utilisateurs.
     *
     * @return Response
     */
    public function listUsers(UsersRepository $usersRepository): Response
    {
        try {
            $this->ensureIsAdmin();

            $users = $usersRepository->findAll();

            return $this->render(
                'admin/users_list.html.twig',
                ['users' => $users]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end listUsers()


    /**
     * Édite un utilisateur.
     *
     * @param Request         $request         La requête
     *                                         HTTP.
     * @param int             $userId          L'identifiant de l'utilisateur.
     * @param UsersRepository $usersRepository Le repository des utilisateurs.
     *
     * @return Response
     */
    public function editUser(Request $request, int $userId, UsersRepository $usersRepository): Response
    {
        try {
            $this->ensureIsAdmin();

            $user = $usersRepository->findById($userId);

            if ($user === null) {
                throw new Exception('Utilisateur non trouvé.', 404);
            }

            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('edit_user_form', $request);

                $role = $request->request->get('role');

                // Mettez à jour le rôle de l'utilisateur.
                $user->setRole($role);

                // Validez l'entité User.
                $validationErrors = $this->validateEntity($user);
                if (empty($validationErrors) === false) {
                    return $this->renderFormWithErrors(
                        'admin/edit_user.html.twig',
                        $validationErrors,
                        ['user' => $user],
                        $this->generateCsrfToken('edit_user_form')
                    );
                }

                $usersRepository->updateUser($user);

                return $this->redirect('/admin/users');
            }//end if

            return $this->render(
                'admin/edit_user.html.twig',
                [
                    'user' => $user,
                    'csrf_token' => $this->generateCsrfToken('edit_user_form'),
                ]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

    }//end editUser()


    /**
     * Supprime un utilisateur.
     *
     * @param Request         $request         La requête
     *                                         HTTP.
     * @param int             $userId          L'identifiant de l'utilisateur.
     * @param UsersRepository $usersRepository Le repository des utilisateurs.
     *
     * @return Response
     */
    public function deleteUser(Request $request, int $userId, UsersRepository $usersRepository): Response
    {
        try {
            $this->ensureIsAdmin();

            $this->isCsrfTokenValidOrFail('delete_user_form', $request);

            $usersRepository->deleteUser($userId);

            return $this->redirect('/admin/users');
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end deleteUser()


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

        // Mettre à jour l'auteur.
        $authorId = (int) $this->cleanInput($request->request->get('author'));
        $post->setAuthor($authorId);

    }//end updatePostFromRequest()


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
            $this->ensureIsAdmin();
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
