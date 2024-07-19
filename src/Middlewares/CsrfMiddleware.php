<?php

namespace App\Middlewares;

use ParagonIE\AntiCSRF\AntiCSRF;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Middleware pour la protection contre les attaques CSRF.
 */
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
        // Démarrer la session si elle n'est pas déjà démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->antiCSRF = new AntiCSRF();
    } //end __construct()


    /**
     * Gère les requêtes entrantes et applique la protection CSRF.
     *
     * Cette méthode vérifie la validité du token CSRF pour les requêtes POST
     * et insère un nouveau token pour les requêtes GET.
     *
     * @param Request  $request La requête entrante.
     * @param callable $next    Le prochain middleware ou contrôleur à exécuter.
     *
     * @return Response La réponse générée après le traitement de la requête.
     * @throws AccessDeniedHttpException Si le token CSRF est invalide pour une requête POST.
     */
    public function handle(Request $request, callable $next): Response
    {
        if ($request->isMethod('POST')) {
            error_log('Validating CSRF token for POST request...');
            error_log('Session ID: ' . session_id());
            error_log('CSRF token in session: ' . ($_SESSION['csrf'] ?? 'not set'));
            error_log('CSRF token in request: ' . $request->request->get('csrf_token'));

            if (!$this->antiCSRF->validateRequest()) {
                error_log('Invalid CSRF token detected.');
                throw new AccessDeniedHttpException('Invalid CSRF token.');
            }
            error_log('CSRF token valid.');
        } elseif ($request->isMethod('GET')) {
            error_log('Inserting CSRF token for GET request...');
            $this->antiCSRF->insertToken();
            error_log('CSRF token inserted: ' . ($_SESSION['csrf'] ?? 'not set'));

            // Vérifiez explicitement que le jeton est bien dans la session
            if (!isset($_SESSION['csrf'])) {
                error_log('CSRF token was not set in the session.');
            } else {
                error_log('CSRF token successfully set in the session: ' . $_SESSION['csrf']);
            }
        }

        return $next($request);
    } //end handle()


}//end class
