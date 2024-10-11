<?php

namespace App\Controllers;

use DateTime;
use App\Models\User;
use Models\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

class AuthController extends BaseController
{


    /**
     * Affiche le formulaire d'inscription et traite la soumission du formulaire.
     *
     * @param Request         $request         L'objet de la requête HTTP contenant les données du formulaire.
     * @param UsersRepository $usersRepository Le repository pour accéder aux utilisateurs.
     *
     * @return Response La réponse HTTP avec le contenu rendu ou une redirection après inscription.
     */
    public function register(Request $request, UsersRepository $usersRepository): Response
    {
        if ($request->isMethod('POST') === true) {
            // Vérifier le token CSRF.
            $submittedToken = $request->request->get('_csrf_token');
            if ($this->isCsrfTokenValid('register_form', $submittedToken) === false) {
                return new Response('Invalid CSRF token.', 403);
            }

            // Récupération des données du formulaire et création de l'objet User.
            $userData = [
                'userId'    => null,
                // Pour un nouvel utilisateur, mettre null ici.
                'lastName'  => $this->cleanInput($request->request->get('last_name')),
                'firstName' => $this->cleanInput($request->request->get('first_name')),
                'email'     => $this->cleanInput($request->request->get('email')),
                'password'  => password_hash($request->request->get('password'), PASSWORD_BCRYPT),
                'role'      => 'user',
                'createdAt' => new DateTime(),
                // Date actuelle pour la création.
                'updatedAt' => null,
                'token'     => null,
                'expireAt'  => null,
                'passwordResetToken'      => null,
                'passwordResetExpiresAt'  => null,
            ];

            // Instancier l'objet User avec les données et le validateur injecté.
            $user = new User($userData, $this->validator);

            // Validation de l'utilisateur.
            $violations = $user->validate();

            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[] = $violation->getMessage();
                }

                return $this->render(
                    'auth/register.html.twig',
                    [
                        'errors' => $errors,
                        'last_name' => $user->getLastName(),
                        'first_name' => $user->getFirstName(),
                        'email' => $user->getEmail(),
                        'csrf_token' => $submittedToken,
                    ]
                );
            }

            // Vérifier si l'utilisateur existe déjà.
            if ($usersRepository->findByEmail($user->getEmail()) !== null) {
                $errors[] = 'Un compte avec cet e-mail existe déjà.';
                return $this->render(
                    'auth/register.html.twig',
                    [
                        'errors' => $errors,
                        'last_name' => $user->getLastName(),
                        'first_name' => $user->getFirstName(),
                        'email' => $user->getEmail(),
                        'csrf_token' => $submittedToken,
                    ]
                );
            }

            // Enregistrer l'utilisateur.
            $user = $usersRepository->createUser($user);

            // Vérifier que l'ID est bien défini.
            if ($user->getId() === null) {
                throw new Exception('Impossible de récupérer l\'ID de l\'utilisateur après l\'enregistrement.');
            }

            // Connecter l'utilisateur en définissant les informations en session.
            $this->sessionService->set('user_id', $user->getId());
            $this->sessionService->set('user_role', $user->getRole());

            return new Response('', 302, ['Location' => '/']);
        }//end if

        // Affichage du formulaire.
        return $this->render(
            'auth/register.html.twig',
            [
                'csrf_token' => $this->generateCsrfToken('register_form'),
            ]
        );

    }//end register()


    /**
     * Gère l'authentification des utilisateurs.
     *
     * @param Request         $request         L'objet de la requête HTTP contenant les données
     *                                         du
     * @param UsersRepository $usersRepository Le repository pour accéder aux utilisateurs.
     *
     * @return Response La réponse HTTP avec redirection ou affichage du formulaire de connexion.
     */
    public function login(Request $request, UsersRepository $usersRepository): Response
    {
        if ($request->isMethod('POST') === true) {
            $submittedToken = $request->request->get('_csrf_token');
            if ($this->isCsrfTokenValid('login_form', $submittedToken) === false) {
                return new Response('Invalid CSRF token.', 403);
            }

            $email    = $this->cleanInput($request->request->get('email'));
            $password = $request->request->get('password');

            // Vérifier si l'utilisateur existe.
            $user = $usersRepository->findByEmail($email);

            if ($user === null || password_verify($password, $user->getPassword()) === false) {
                $errors = ['Identifiants incorrects'];

                return $this->render(
                    'auth/login.html.twig',
                    [
                        'errors' => $errors,
                        'email'  => $email,
                        'csrf_token' => $submittedToken,
                    ]
                );
            }

            // Utiliser le SessionService pour stocker les informations de l'utilisateur connecté.
            $this->sessionService->set('user_id', $user->getId());
            $this->sessionService->set('user_role', $user->getRole());

            return new Response('', 302, ['Location' => '/']);
        }//end if

        // Récupérer le message de succès depuis la session, s'il existe.
        $successMessage = $this->sessionService->get('success_message', null);

        // Supprimer le message de la session pour qu'il ne s'affiche qu'une seule fois.
        $this->sessionService->remove('success_message');

        // Affichage du formulaire de connexion.
        return $this->render(
            'auth/login.html.twig',
            [
                'csrf_token' => $this->generateCsrfToken('login_form'),
                'successMessage' => $successMessage,
            ]
        );

    }//end login()


    /**
     * Déconnecte l'utilisateur.
     *
     * @return Response Redirection vers la page d'accueil après déconnexion.
     */
    public function logout(): Response
    {
        // Utiliser le SessionService pour détruire la session de l'utilisateur.
        $this->sessionService->destroy();

        return new Response('', 302, ['Location' => '/']);

    }//end logout()


    /**
     * Affiche le formulaire de demande de réinitialisation du mot de passe et traite la soumission du formulaire.
     *
     * @param Request         $request         L'objet de la requête HTTP.
     * @param UsersRepository $usersRepository Le repository pour accéder aux utilisateurs.
     *
     * @return Response La réponse HTTP.
     */
    public function passwordResetRequest(Request $request, UsersRepository $usersRepository) : Response
    {
        if ($request->isMethod('POST') === true) {
            $email = trim($request->request->get('email'));

            // Rechercher l'utilisateur par e-mail.
            $user = $usersRepository->findByEmail($email);

            if ($user !== null) {
                // Générer le token et l'envoyer par e-mail.
                $this->sendPasswordResetEmail($user, $usersRepository);
            }

            // Toujours afficher un message de succès pour éviter de révéler si l'e-mail existe.
            // Stocker le message de succès dans la session.
            $this->sessionService->set('success_message', 'Si un compte est associé à cette adresse e-mail, un message a été envoyé avec les instructions pour réinitialiser votre mot de passe. Ce lien expirera dans 1 heure.');

            // Rediriger vers la page de succès.
            return new Response('', 302, ['Location' => '/password-reset-request/success']);
        }

        // Afficher le formulaire de demande de réinitialisation.
        return $this->render(
            'auth/password_reset_request.html.twig',
            [
                'csrf_token' => $this->generateCsrfToken('password_reset_request_form'),
            ]
        );

    }//end passwordResetRequest()


    /**
     * Affiche la page de succès après une demande de réinitialisation de mot de passe.
     *
     * @return Response La réponse HTTP.
     */
    public function passwordResetRequestSuccess(): Response
    {
        // Récupérer le message de succès depuis la session.
        $successMessage = $this->sessionService->get('success_message', null);

        // Supprimer le message de la session pour qu'il ne s'affiche qu'une seule fois.
        $this->sessionService->remove('success_message');

        // Afficher la vue de succès.
        return $this->render(
            'auth/password_reset_request_success.html.twig',
            [
                'message' => $successMessage,
            ]
        );

    }//end passwordResetRequestSuccess()


    /**
     * Envoie l'e-mail de réinitialisation de mot de passe à l'utilisateur.
     *
     * @param User            $user            L'utilisateur qui a demandé la réinitialisation.
     * @param UsersRepository $usersRepository Le repository pour accéder aux utilisateurs.
     *
     * @return void
     */
    private function sendPasswordResetEmail(User $user, UsersRepository $usersRepository): void
    {
        // Générer un token sécurisé.
        $token = bin2hex(random_bytes(32));

        // Définir une date d'expiration (par exemple, 1 heure).
        $expiresAt = (new DateTime())->modify('+1 hour');

        // Enregistrer le token et l'expiration dans la base de données.
        $user->setPasswordResetToken($token);
        $user->setPasswordResetExpiresAt($expiresAt);
        $usersRepository->updateUser($user);

        // Générer le lien de réinitialisation.
        $resetLink = $this->generateResetLink($token);

        // Ajouter une instruction pour enregistrer ou afficher le lien.
        error_log("Lien de réinitialisation généré : {$resetLink}");

        // Envoyer l'e-mail (Vous devrez implémenter le service d'envoi d'e-mails).
        $subject = 'Réinitialisation de votre mot de passe';
        $body    = $this->renderTemplate(
            'auth/password_reset_email.html.twig',
            [
                'reset_link' => $resetLink,
            ]
        );

        // Utilisez votre service d'envoi d'e-mails.
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

    }//end sendPasswordResetEmail()


    /**
     * Génère le lien de réinitialisation de mot de passe.
     *
     * @param string $token Le token de réinitialisation du mot de passe.
     *
     * @return string Le lien complet pour la réinitialisation du mot de passe.
     */
    private function generateResetLink(string $token): string
    {
        $baseUrl = $this->getBaseUrl();
        return "{$baseUrl}/password-reset/{$token}";

    }//end generateResetLink()


    /**
     * Obtient l'URL de base de l'application.
     *
     * @return string L'URL de base.
     */
    private function getBaseUrl(): string
    {
        $scheme = 'http';

        if (isset($_SERVER['HTTPS']) === true && ($_SERVER['HTTPS'] === 'on' || $_SERVER['HTTPS'] === '1')) {
            $scheme = 'https';
        } else if (isset($_SERVER['SERVER_PORT']) === true && $_SERVER['SERVER_PORT'] === '443') {
            $scheme = 'https';
        } else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) === true && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            $scheme = 'https';
        } else if (isset($_SERVER['REQUEST_SCHEME']) === true && strtolower($_SERVER['REQUEST_SCHEME']) === 'https') {
            $scheme = 'https';
        }

        $host = 'localhost';
        if (isset($_SERVER['HTTP_HOST']) === true) {
            $host = $_SERVER['HTTP_HOST'];
        } else if (isset($_SERVER['SERVER_NAME']) === true) {
            $host = $_SERVER['SERVER_NAME'];
        }

        return $scheme.'://'.$host;

    }//end getBaseUrl()


    /**
     * Gère la réinitialisation du mot de passe.
     *
     * @param Request         $request         L'objet de la requête HTTP.
     * @param UsersRepository $usersRepository Le repository pour accéder aux utilisateurs.
     * @param string          $token           Le token de réinitialisation du mot de passe.
     *
     * @return Response La réponse HTTP.
     */
    public function passwordReset(Request $request, UsersRepository $usersRepository, string $token): Response
    {
        // Rechercher l'utilisateur avec ce token.
        $user = $usersRepository->findByPasswordResetToken($token);

        if ($user === null || $user->getPasswordResetExpiresAt() < new DateTime()) {
            // Token invalide ou expiré.
            return $this->render('auth/password_reset_invalid.html.twig');
        }

        if ($request->isMethod('POST') === true) {
            $submittedToken = $request->request->get('_csrf_token');
            if ($this->isCsrfTokenValid('password_reset_form', $submittedToken) === false) {
                return new Response('Invalid CSRF token.', 403);
            }

            $newPassword     = trim($request->request->get('password'));
            $confirmPassword = trim($request->request->get('confirm_password'));

            $errors = [];

            // Validation des mots de passe.
            if (empty($newPassword) === true || empty($confirmPassword) === true) {
                $errors[] = 'Tous les champs sont obligatoires.';
            } else if ($newPassword !== $confirmPassword) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            } else if (strlen($newPassword) < 6) {
                // Vous pouvez ajuster la longueur minimale.
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
            }

            if (empty($errors) === true) {
                // Hacher le nouveau mot de passe.
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Mettre à jour le mot de passe et supprimer le token.
                $user->setPassword($hashedPassword);
                $user->setPasswordResetToken(null);
                $user->setPasswordResetExpiresAt(null);
                $user->setUpdatedAt(new \DateTime());

                // Mettre à jour l'utilisateur dans la base de données.
                $usersRepository->updateUser($user);

                // Stocker le message de succès dans la session.
                $this->sessionService->set('success_message', 'Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter.');

                // Rediriger vers la page de connexion.
                return new Response('', 302, ['Location' => '/login']);
            }

            // Rendre le formulaire avec les erreurs.
            return $this->render(
                'auth/password_reset.html.twig',
                [
                    'errors' => $errors,
                    'csrf_token' => $this->generateCsrfToken('password_reset_form'),
                    'token' => $token,
                ]
            );
        }//end if

        // Afficher le formulaire de réinitialisation.
        return $this->render('auth/password_reset.html.twig', ['token' => $token, 'csrf_token' => $this->generateCsrfToken('password_reset_form'),]);

    }//end passwordReset()


}//end class
