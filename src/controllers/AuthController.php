<?php

namespace App\Controllers;

use DateTime;
use App\Models\User;
use Models\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
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
        try {
            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('register_form', $request);

                $userData = $this->extractUserData($request, ['role' => 'user']);
                $user     = new User($userData);

                $validationErrors = $this->validateEntity($user);
                if (empty($validationErrors) === false) {
                    return $this->renderFormWithErrors('auth/register.html.twig', $validationErrors, $userData, $this->generateCsrfToken('register_form'));
                }

                if ($usersRepository->findByEmail($user->getEmail()) === true) {
                    return $this->renderFormWithErrors(
                        'auth/register.html.twig',
                        ['Cet email est déjà utilisé.'],
                        $userData,
                        $this->generateCsrfToken('register_form')
                    );
                }

                $usersRepository->createUser($user);
                $this->loginUser($user);

                return $this->redirect('/');
            }//end if

            return $this->render('auth/register.html.twig', ['csrf_token' => $this->generateCsrfToken('register_form')]);
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

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
        try {
            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('login_form', $request);

                $email    = $this->cleanInput($request->request->get('email'));
                $password = $request->request->get('password');
                $user     = $usersRepository->findByEmail($email);

                if ($this->isValidLogin($user, $password) === false) {
                    return $this->renderFormWithErrors(
                        'auth/login.html.twig',
                        ['Identifiants incorrects.'],
                        ['email' => $email],
                        $this->generateCsrfToken('login_form')
                    );
                }

                $this->loginUser($user);

                return $this->redirect('/');
            }

            return $this->renderLoginPage();
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

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
        return $this->redirect('/');

    }//end logout()


    /**
     * Affiche le formulaire de demande de réinitialisation du mot de passe et traite la soumission du formulaire.
     *
     * @param Request         $request         L'objet de la requête HTTP.
     * @param UsersRepository $usersRepository Le repository pour accéder aux utilisateurs.
     *
     * @return Response La réponse HTTP.
     */
    public function passwordResetRequest(Request $request, UsersRepository $usersRepository): Response
    {
        try {
            if ($this->isPostRequest($request) === true) {
                $email = $this->cleanInput($request->request->get('email'));

                $user = $usersRepository->findByEmail($email);
                if ($user !== null) {
                    $this->initiatePasswordReset($user, $usersRepository);
                }

                $this->setSuccessMessage('Si cet email est valide, un message vous a été envoyé.');
                return $this->redirect('/password-reset-request/success');
            }

            return $this->render(
                'auth/password_reset_request.html.twig',
                ['csrf_token' => $this->generateCsrfToken('password_reset_request_form')]
            );
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }

    }//end passwordResetRequest()

    /**
     * Affiche la page de confirmation après une demande de réinitialisation de mot de passe.
     *
     * @return Response La réponse HTTP avec le contenu rendu.
     */
    public function passwordResetRequestSuccess(): Response
    {
        $message = $this->getAndRemoveSuccessMessage();

        return $this->render('auth/password_reset_request_success.html.twig', [
            'message' => $message,
        ]);
    }


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
        try {
            $user = $usersRepository->findByPwdResetToken($token);
            if ($user === false || $this->isTokenValidForPasswordReset($user) === false) {
                return $this->render('auth/password_reset_invalid.html.twig');
            }

            if ($this->isPostRequest($request) === true) {
                $this->isCsrfTokenValidOrFail('password_reset_form', $request);

                $errors = $this->processPasswordReset($request, $user, $usersRepository);
                if (empty($errors) === true) {
                    $this->setSuccessMessage('Mot de passe réinitialisé avec succès.');
                    return $this->redirect('/login');
                }

                return $this->renderPasswordResetForm($token, $errors);
            }

            return $this->renderPasswordResetForm($token);
        } catch (Exception $e) {
            return $this->renderError($e->getMessage(), $e->getCode());
        }//end try

    }//end passwordReset()


    /**
     * Extrait les données utilisateur de la requête.
     *
     * @param Request $request        L'objet de la requête
     *                                HTTP.
     * @param array   $additionalData Les données supplémentaires à
     *                                inclure.
     *
     * @return array Les données utilisateur extraites.
     */
    private function extractUserData(Request $request, array $additionalData=[]): array
    {
        return array_merge(
            [
                'userId' => null,
                'lastName' => $this->cleanInput($request->request->get('last_name')),
                'firstName' => $this->cleanInput($request->request->get('first_name')),
                'email' => $this->cleanInput($request->request->get('email')),
                'password' => password_hash($request->request->get('password'), PASSWORD_BCRYPT),
                'createdAt' => new DateTime(),
                'updatedAt' => null,
                'token' => null,
                'expireAt' => null,
                'pwdResetToken' => null,
                'pwdResetExpiresAt' => null,
            ],
            $additionalData
        );

    }//end extractUserData()


    /**
     * Connecte un utilisateur.
     *
     * @param User $user L'utilisateur à connecter.
     *
     * @return void
     */
    private function loginUser(User $user): void
    {
        $this->sessionService->set('user_id', $user->getId());
        $this->sessionService->set('user_role', $user->getRole());
        $this->sessionService->set('user_first_name', $user->getFirstName());
        $this->sessionService->set('user_last_name', $user->getLastName());

    }//end loginUser()


    /**
     * Vérifie les identifiants de connexion.
     *
     * @param User|null $user     L'utilisateur à vérifier.
     * @param string    $password Le mot de passe fourni.
     *
     * @return bool Retourne true si les identifiants sont valides, sinon false.
     */
    private function isValidLogin(?User $user, string $password): bool
    {
        return $user !== null && password_verify($password, $user->getPassword());

    }//end isValidLogin()


    /**
     * Rend la page de connexion avec un message de succès, le cas échéant.
     *
     * @return Response
     */
    private function renderLoginPage(): Response
    {
        $successMessage = $this->getAndRemoveSuccessMessage();

        return $this->render(
            'auth/login.html.twig',
            [
                'csrf_token' => $this->generateCsrfToken('login_form'),
                'successMessage' => $successMessage,
            ]
        );

    }//end renderLoginPage()


    /**
     * Gère l'initialisation de la réinitialisation du mot de passe pour un utilisateur.
     *
     * @param User            $user            L'utilisateur concerné.
     * @param UsersRepository $usersRepository Le repository pour sauvegarder les modifications.
     *
     * @return void
     */
    private function initiatePasswordReset(User $user, UsersRepository $usersRepository): void
    {
        $token     = bin2hex(random_bytes(32));
        $expiresAt = (new DateTime())->modify('+1 hour');

        $user->setPwdResetToken($token);
        $user->setPwdResetExpiresAt($expiresAt);
        $usersRepository->updateUser($user);

        $resetLink = $this->generateResetLink($token);
        $this->sendPasswordResetEmail($user, $resetLink);

    }//end initiatePasswordReset()


    /**
     * Traite les données soumises pour la réinitialisation du mot de passe.
     *
     * @param Request         $request         La requête HTTP contenant les données.
     * @param User            $user            L'utilisateur concerné.
     * @param UsersRepository $usersRepository Le repository pour sauvegarder les modifications.
     *
     * @return array Liste des erreurs rencontrées lors de la réinitialisation.
     */
    private function processPasswordReset(Request $request, User $user, UsersRepository $usersRepository): array
    {
        $newPassword     = trim($request->request->get('password'));
        $confirmPassword = trim($request->request->get('confirm_password'));

        $errors = $this->validatePasswordResetInput($newPassword, $confirmPassword);

        if (empty($errors) === true) {
            $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));
            $user->setPwdResetToken(null);
            $user->setPwdResetExpiresAt(null);
            $user->setUpdatedAt(new DateTime());

            $usersRepository->updateUser($user);
        }

        return $errors;

    }//end processPasswordReset()


    /**
     * Rend le formulaire de réinitialisation du mot de passe.
     *
     * @param string $token  Le token de réinitialisation du mot de passe.
     * @param array  $errors Liste des erreurs à afficher dans le formulaire (par défaut vide).
     *
     * @return Response La réponse HTTP contenant le formulaire rendu.
     */
    private function renderPasswordResetForm(string $token, array $errors=[]): Response
    {
        return $this->render(
            'auth/password_reset.html.twig',
            [
                'csrf_token' => $this->generateCsrfToken('password_reset_form'),
                'token'      => $token,
                'errors'     => $errors,
            ]
        );

    }//end renderPasswordResetForm()


    /**
     * Valide les mots de passe fournis lors de la réinitialisation.
     *
     * @param string $password        Le nouveau mot de passe.
     * @param string $confirmPassword Le mot de passe de confirmation.
     *
     * @return array Liste des erreurs de validation.
     */
    private function validatePasswordResetInput(string $password, string $confirmPassword): array
    {
        $errors = [];
        if (empty($password) === true || empty($confirmPassword) === true) {
            $errors[] = 'Tous les champs sont obligatoires.';
        } else if ($password !== $confirmPassword) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        } else if (strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères.';
        }

        return $errors;

    }//end validatePasswordResetInput()


    /**
     * Vérifie si le token est valide pour la réinitialisation du mot de passe.
     *
     * @param User|null $user L'utilisateur pour lequel vérifier le token.
     *
     * @return bool Retourne true si le token est valide, sinon false.
     */
    private function isTokenValidForPasswordReset(?User $user): bool
    {
        return $user !== null && $user->getPwdResetExpiresAt() >= new DateTime();

    }//end isTokenValidForPasswordReset()


    /**
     * Génère le lien de réinitialisation de mot de passe.
     *
     * @param string $token Le token de réinitialisation du mot de passe.
     *
     * @return string Le lien complet pour la réinitialisation du mot de passe.
     */
    private function generateResetLink(string $token): string
    {
        // Passer le troisième argument à UrlGenerator::ABSOLUTE_URL pour obtenir une URL complète.
        return $this->urlGeneratorService->generateUrl('password_reset', ['token' => $token], UrlGenerator::ABSOLUTE_URL);

    }//end generateResetLink()


    /**
     * Envoie l'e-mail de réinitialisation de mot de passe à l'utilisateur.
     *
     * @param User   $user      L'utilisateur qui a demandé la réinitialisation.
     * @param string $resetLink Le lien de réinitialisation généré pour l'utilisateur.
     *
     * @return void
     */
    private function sendPasswordResetEmail(User $user, string $resetLink): void
    {
        // Envoyer l'e-mail (Vous devrez implémenter le service d'envoi d'e-mails).
        $subject = 'Réinitialisation de votre mot de passe';
        $body    = $this->renderTemplate(
            'emails/password_reset_email.html.twig',
            [
                'reset_link' => $resetLink,
            ]
        );

        // Utilisez votre service d'envoi d'e-mails.
        $this->emailService->sendEmail($user->getEmail(), $subject, $body);

    }//end sendPasswordResetEmail()


}//end class
