<?php

namespace App\Middlewares;

use ParagonIE\AntiCSRF\AntiCSRF;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CsrfMiddleware
{
    /**
     * Instance pour gérer la protection contre les attaques CSRF.
     *
     * @var AntiCSRF
     */
    private AntiCSRF $antiCSRF;


    /**
     * Constructeur de la classe.
     *
     * Initialise l'instance AntiCSRF pour la protection contre les attaques CSRF.
     */
    public function __construct()
    {
        $this->antiCSRF = new AntiCSRF();
    } //end_construct().


    /**
     * Gère les requêtes entrantes et applique la protection CSRF.
     *
     * Cette méthode vérifie la validité du token CSRF pour les requêtes POST
     * et insère un nouveau token pour les requêtes GET.
     *
     * @param Request   $request La requête entrante.
     * @param callable  $next    Le prochain middleware ou contrôleur à exécuter.
     * @return Response La réponse générée après le traitement de la requête.
     * @throws AccessDeniedHttpException Si le token CSRF est invalide pour une requête POST.
     */
    public function handle(Request $request, callable $next): Response
    {
        if ($request->isMethod('POST') === true) {
            if (!$this->antiCSRF->validateRequest()) {
                throw new AccessDeniedHttpException('Invalid CSRF token.');
            }
        }

        if ($request->isMethod('GET') === true) {
            $this->antiCSRF->insertToken();
        }

        return $next($request);
    }
}
