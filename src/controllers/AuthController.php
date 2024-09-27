<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use DateTime;
use Models\UsersRepository;

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

            // Récupération et création de l'objet User.
            $user = new User();
            $user->setLastName($this->cleanInput($request->request->get('last_name')));
            $user->setFirstName($this->cleanInput($request->request->get('first_name')));
            $user->setEmail($this->cleanInput($request->request->get('email')));
            $user->setPassword(password_hash($request->request->get('password'), PASSWORD_BCRYPT));
            $user->setRole('user');

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
            $usersRepository->createUser($user);
            return new Response('', 302, ['Location' => '/login']);
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
                $errors[] = 'Adresse e-mail ou mot de passe incorrect.';
                return $this->render(
                    'auth/login.html.twig',
                    [
                        'errors' => $errors,
                        'email' => $email,
                        'csrf_token' => $submittedToken,
                    ]
                );
            }

            // Enregistrer l'utilisateur en session.
            $_SESSION['user_id']   = $user->getId();
            $_SESSION['user_role'] = $user->getRole();

            return new Response('', 302, ['Location' => '/']);
        }//end if

        // Affichage du formulaire de connexion.
        return $this->render(
            'auth/login.html.twig',
            [
                'csrf_token' => $this->generateCsrfToken('login_form'),
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
        // Détruire la session de l'utilisateur.
        session_unset();
        session_destroy();

        return new Response('', 302, ['Location' => '/']);

    }//end logout()


}//end class
