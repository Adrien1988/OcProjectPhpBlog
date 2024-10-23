<?php

namespace App\Controllers;

use Twig\Environment;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\EmailService;
use App\Services\SessionService;
use App\Services\SecurityService;
use App\Services\UrlGeneratorService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;




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
     * Service de session pour gérer les sessions utilisateur.
     *
     * @var SessionService
     */
    protected SessionService $sessionService;

    /**
     * Service pour la gestion des envois de mail.
     *
     * @var EmailService
     */
    protected EmailService $emailService;

    /**
     * Le validateur Symfony pour la validation des entités.
     *
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * Service de génération d'URL.
     *
     * @var UrlGeneratorService
     */
    protected UrlGeneratorService $urlGeneratorService;


    /**
     * Constructeur de la classe.
     * Initialise l'instance Twig pour le rendu des templates.
     *
     * @param Environment         $twig                Instance de l'environnement Twig.
     * @param SecurityService     $securityService     Le service de sécurité pour la protection contre les
     *                                                 attaques XSS.
     * @param EnvService          $envService          Instance du service de gestion des variables d'environnement.
     * @param CsrfService         $csrfService         Service pour la gestion des tokens CSRF.
     * @param SessionService      $sessionService      L'instance de SessionService pour la gestion des sessions.
     * @param EmailService        $emailService        Service pour l'envoi de mail.
     * @param ValidatorInterface  $validator           Le validateur Symfony pour la validation des
     *                                                 entités.
     * @param UrlGeneratorService $urlGeneratorService Service pour générer les URLs de base.
     */
    public function __construct(
        Environment $twig,
        SecurityService $securityService,
        EnvService $envService,
        CsrfService $csrfService,
        SessionService $sessionService,
        EmailService $emailService,
        ValidatorInterface $validator,
        UrlGeneratorService $urlGeneratorService
    ) {
        $this->twig            = $twig;
        $this->securityService = $securityService;
        $this->envService      = $envService;
        $this->csrfService     = $csrfService;
        $this->sessionService  = $sessionService;
        $this->emailService    = $emailService;
        $this->validator       = $validator;
        $this->urlGeneratorService = $urlGeneratorService;

        if ($this->sessionService->isStarted() === false) {
            $this->sessionService->start();
        }

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
     * Rends un template Twig en une chaîne de caractères.
     *
     * @param string $template Le chemin du template Twig, relatif au dossier des templates.
     * @param array  $data     Les données à passer au
     *                         template.
     *
     * @return string Le contenu rendu du template.
     */
    protected function renderTemplate(string $template, array $data=[]): string
    {
        return $this->twig->render($template, $data);

    }//end renderTemplate()


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


    /**
     * Récupère une valeur de la session.
     *
     * @param string $key     La clé de la valeur à récupérer.
     * @param mixed  $default La valeur par défaut si la clé n'existe pas.
     *
     * @return mixed La valeur de la session ou la valeur par défaut.
     */
    protected function getSessionValue(string $key, $default=null)
    {
        return $this->sessionService->get($key, $default);

    }//end getSessionValue()


    /**
     * Définit une valeur dans la session.
     *
     * @param string $key   La clé de la valeur à définir.
     * @param mixed  $value La valeur à définir.
     *
     * @return void
     */
    protected function setSessionValue(string $key, $value): void
    {
        $this->sessionService->set($key, $value);

    }//end setSessionValue()


    /**
     * Supprime une valeur de la session.
     *
     * @param string $key La clé de la valeur à supprimer.
     *
     * @return void
     */
    protected function removeSessionValue(string $key): void
    {
        $this->sessionService->remove($key);

    }//end removeSessionValue()


    /**
     * Vérifie si une clé existe dans la session.
     *
     * @param string $key La clé à vérifier.
     *
     * @return bool True si la clé existe, false sinon.
     */
    protected function hasSessionKey(string $key): bool
    {
        return $this->sessionService->has($key);

    }//end hasSessionKey()


    /**
     * Obtient l'instance du validateur Symfony.
     *
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->validator;

    }//end getValidator()


}//end class
