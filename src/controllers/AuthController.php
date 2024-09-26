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

            // Récupération et nettoyage des données du formulaire.
            $lastName = $this->cleanInput($request->request->get('last_name'));
            $firstName = $this->cleanInput($request->request->get('first_name'));
            $email = $this->cleanInput($request->request->get('email'));
            $password = $request->request->get('password');
            $confirmPassword=$request->request->get('confirm_password');

            // Validation des données.
            $errors = [];

            if (empty($lastname)) {
                $errors[] = 'Le nom est requis.';
            }
        }
    }
}
