<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\SecurityService;
use Symfony\Component\HttpFoundation\Response;




class BaseController
{

    /**
     * Instance de l'environnement Twig pour le rendu des templates.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * Service de sécurité pour la protection contre les attaques XSS.
     *
     * @var SecurityService
     */
    protected $securityService;

    /**
     * Service pour charger les variables d'environnement.
     *
     * @var EnvService
     */
    protected EnvService $envService;

    /**
     * Service pour la gestion des tokens CSRF.
     *
     * @var CsrfService
     */
    protected $csrfService;


    /**
     * Constructeur de la classe.
     * Initialise l'instance Twig pour le rendu des templates.
     *
     * @param Environment     $twig            Instance de l'environnement Twig.
     * @param SecurityService $securityService Le service de sécurité pour la protection contre les attaques XSS.
     * @param EnvService      $envService      Instance du service de gestion des variables d'environnement.
     * @param CsrfService     $csrfService     Service pour la gestion des tokens CSRF.
     */
    public function __construct(Environment $twig, SecurityService $securityService, EnvService $envService, CsrfService $csrfService)
    {
        $this->twig            = $twig;
        $this->securityService = $securityService;
        $this->envService      = $envService;
        $this->csrfService     = $csrfService;

    }//end __construct()


    /**
     * Rendre un template Twig avec les données fournies.
     *
     * @param string $template Le nom du template.
     * @param array  $data     Les données à passer
     *                         au
     *
     * @return Response
     */
    protected function render(string $template, array $data=[]): Response
    {
        $content = $this->twig->render($template, $data);
        return new Response($content);

    }//end render()


    /**
     * Générer un token CSRF.
     *
     * @param string $tokenId L'identifiant du token.
     *
     * @return string
     */
    protected function generateCsrfToken(string $tokenId): string
    {
        return $this->csrfService->generateToken($tokenId);

    }//end generateCsrfToken()


    /**
     * Méthode pour valider les tokens CSRF.
     *
     * @param string $tokenId        L'identifiant du token.
     * @param string $submittedToken Le token soumis.
     *
     * @return bool
     */
    protected function isCsrfTokenValid(string $tokenId, string $submittedToken): bool
    {
        return $this->csrfService->isTokenValid($tokenId, $submittedToken);

    }//end isCsrfTokenValid()


    /**
     * Méthode pour nettoyer les entrées utilisateur avec le service de sécurité.
     *
     * @param string $input L'entrée à nettoyer.
     *
     * @return string
     */
    protected function cleanInput(string $input): string
    {
        return $this->securityService->cleanInput($input);

    }//end cleanInput()


    /**
     * Méthode pour obtenir une variable d'environnement.
     *
     * @param string      $key     La clé de la
     *                             variable
     * @param string|null $default La valeur par défaut si la clé n'est pas définie.
     *
     * @return string|null
     */
    protected function getEnv(string $key, ?string $default=null): ?string
    {
        return $this->envService->getEnv($key, $default);

    }//end getEnv()


}//end class
