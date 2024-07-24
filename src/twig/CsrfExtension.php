<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use App\Services\CsrfService;

class CsrfExtension extends AbstractExtension
{

    private $csrfService;


    public function __construct(CsrfService $csrfService)
    {
        $this->csrfService = $csrfService;

    }//end __construct()


    public function getFunctions()
    {
        return [
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];

    }//end getFunctions()


    public function getCsrfToken(string $tokenId): string
    {
        return $this->csrfService->generateToken($tokenId);

    }//end getCsrfToken()


}//end class
