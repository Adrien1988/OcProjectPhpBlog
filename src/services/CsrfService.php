<?php

namespace App\Services;

use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfService
{

    /**
     * Gestionnaire de jetons CSRF.
     *
     * Le service utilisé pour gérer et valider les jetons CSRF.
     *
     * @var CsrfTokenManager
     */
    private $csrfTokenManager;


    /**
     * Constructeur de la classe.
     *
     * Initialise le gestionnaire de jetons CSRF.
     */
    public function __construct()
    {
        $this->csrfTokenManager = new CsrfTokenManager();

    }//end __construct()


    /**
     * Génère un jeton CSRF.
     *
     * @param string $tokenId L'identifiant du jeton.
     *
     * @return string La valeur du jeton CSRF généré.
     */
    public function generateToken(string $tokenId): string
    {
        return $this->csrfTokenManager->getToken($tokenId)->getValue();

    }//end generateToken()


    /**
     * Vérifie si un jeton CSRF est valide.
     *
     * @param string $tokenId    L'identifiant du jeton.
     * @param string $tokenValue La valeur du jeton.
     *
     * @return bool True si le jeton est valide, false sinon.
     */
    public function isTokenValid(string $tokenId, string $tokenValue): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($tokenId, $tokenValue));

    }//end isTokenValid()


}//end class
