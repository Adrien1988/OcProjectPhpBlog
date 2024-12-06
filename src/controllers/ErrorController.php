<?php

namespace App\Controllers;

use Twig\Environment;
use Models\PostsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur pour la page d'accueil.
 */
class ErrorController extends BaseController
{


    /**
     * Gère les erreurs HTTP et affiche les pages correspondantes.
     *
     * @param int         $code    Code HTTP de l'erreur (400, 403, 404, 500).
     * @param string|null $message Message d'erreur à afficher (optionnel).
     *
     * @return Response Page d'erreur.
     */
    public function handle(int $code, ?string $message=null): Response
    {
        $template = match ($code) {
            400 => 'errors/400.html.twig',
            403 => 'errors/403.html.twig',
            404 => 'errors/404.html.twig',
            500 => 'errors/500.html.twig',
            default => 'errors/500.html.twig',
        };

        return $this->render($template, ['code' => $code, 'error-message' => $message,]);

    }//end handle()


}//end class
