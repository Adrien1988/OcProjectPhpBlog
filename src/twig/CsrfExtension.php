<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Services\CsrfService;

class CsrfExtension extends AbstractExtension
{

    /**
     * Service CSRF.
     *
     * Le service utilisé pour gérer et valider les jetons CSRF.
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
     * Retourne les fonctions Twig personnalisées.
     *
     * Cette méthode enregistre une nouvelle fonction Twig appelée 'csrf_token' qui appelle la méthode 'getCsrfToken'.
     *
     * @return array Les fonctions Twig personnalisées.
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];

    }//end getFunctions()


    /**
     * Génère et retourne un jeton CSRF.
     *
     * @param string $tokenId L'identifiant du jeton CSRF.
     *
     * @return string La valeur du jeton CSRF généré.
     */
    public function getCsrfToken(string $tokenId): string
    {
        return $this->csrfService->generateToken($tokenId);

    }//end getCsrfToken()


}//end class
