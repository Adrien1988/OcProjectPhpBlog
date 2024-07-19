<?php

namespace App\Twig;

use ParagonIE\AntiCSRF\AntiCSRF;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extension Twig pour générer des tokens CSRF.
 */
class CsrfExtension extends AbstractExtension
{

    /**
     * Instance de AntiCSRF.
     *
     * @var AntiCSRF
     */
    private AntiCSRF $antiCSRF;


    /**
     * Constructeur de la classe.
     */
    public function __construct()
    {
        // Assurez-vous que la session est démarrée
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->antiCSRF = new AntiCSRF();

    }//end __construct()


    /**
     * Retourne la liste des fonctions Twig définies par cette extension.
     *
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('csrf_token', [$this, 'getCsrfToken']),
        ];

    }//end getFunctions()


    /**
     * Génère un token CSRF.
     *
     * @return string
     */
    public function getCsrfToken(): string
    {
        $token = $this->antiCSRF->insertToken();
        error_log('CSRF token generated and inserted into session: ' . $_SESSION['csrf']);
        return $token;

    }//end getCsrfToken()


}//end class
