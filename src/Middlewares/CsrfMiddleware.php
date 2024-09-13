<?php

namespace App\Middlewares;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\CsrfService;

/**
 * Middleware pour la protection contre les attaques CSRF.
 */
class CsrfMiddleware
{

    /**
     * Service CSRF.
     *
     * Le service CSRF utilisé pour la validation des jetons CSRF.
     *
     * @var CsrfService
     */
    private $csrfService;


    /**
     * Constructeur de la classe.
     *
     * Initialise le service CSRF.
     *
     * @param CsrfService $csrfService Le service CSRF pour la validation des jetons CSRF.
     */
    public function __construct(CsrfService $csrfService)
    {
        $this->csrfService = $csrfService;

    }//end __construct()


    /**
     * Gère la requête HTTP.
     *
     * Vérifie si la méthode de la requête est POST et valide le jeton CSRF.
     * Si le jeton CSRF est invalide, retourne une réponse HTTP 400.
     *
     * @param Request  $request La requête HTTP
     *                          courante.
     * @param callable $next    Le prochain middleware ou la prochaine action à
     *                          exécuter.
     *
     * @return Response La réponse HTTP, soit la suivante, soit une erreur si le jeton CSRF est invalide.
     */
    public function handle(Request $request, callable $next)
    {
        if ($request->isMethod('POST') === true) {
             // Récupère l'identifiant du formulaire
             $formTokenId = $request->request->get('_csrf_token_id');
             $submittedToken = $request->request->get('_csrf_token');
             
             // Vérifier si le token est présent et valide
             if ($formTokenId && $submittedToken && !$this->csrfService->isTokenValid($formTokenId, $submittedToken)) {
                 return new Response('Invalid CSRF token.', 400);
             }
        }

        return $next($request);

    }//end handle()


}//end class
