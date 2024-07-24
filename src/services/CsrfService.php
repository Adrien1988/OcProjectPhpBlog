<?php

namespace App\Services;

use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfToken;

class CsrfService
{

    private $csrfTokenManager;


    public function __construct()
    {
        $this->csrfTokenManager = new CsrfTokenManager();

    }//end __construct()


    public function generateToken(string $tokenId): string
    {
        return $this->csrfTokenManager->getToken($tokenId)->getValue();

    }//end generateToken()


    public function isTokenValid(string $tokenId, string $tokenValue): bool
    {
        return $this->csrfTokenManager->isTokenValid(new CsrfToken($tokenId, $tokenValue));

    }//end isTokenValid()


}//end class
