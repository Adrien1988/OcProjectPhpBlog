<?php

namespace App\Services;

use voku\helper\AntiXSS;

class SecurityService
{
    /**
     * Instance pour gérer la protection contre les attaques XSS.
     *
     * @var AntiXSS
     */
    private AntiXSS $antiXSS;


    /**
     * Constructeur de la classe.
     *
     * Initialise l'instance AntiXSS pour la protection contre les attaques XSS.
     */
    public function __construct()
    {
        $this->antiXSS = new AntiXSS();

    } // end __construct().


    /**
     * Nettoie une entrée utilisateur pour empêcher les attaques XSS.
     *
     * Cette méthode utilise l'instance AntiXSS pour nettoyer l'entrée fournie.
     *
     * @param string $input L'entrée utilisateur à nettoyer.
     * @return string La chaîne nettoyée.
     */
    public function cleanInput(string $input): string
    {
        return $this->antiXSS->xss_clean($input);
    }
}
