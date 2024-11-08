<?php

namespace App\Init;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Twig\CsrfExtension;
use App\Services\CsrfService;

/**
 * Initialise l'environnement Twig pour le rendu des templates.
 */
class TwigInit
{


    /**
     * Initialise Twig avec les extensions nécessaires.
     *
     * @param CsrfService $csrfService Le service CSRF pour la protection des formulaires.
     *
     * @return Environment L'environnement Twig initialisé.
     */
    public function initialize(CsrfService $csrfService): Environment
    {
        $loader = new FilesystemLoader(__DIR__.'/../../templates');
        $twig   = new Environment($loader, ['cache' => false, 'auto_reload' => true]);
        $twig->addExtension(new CsrfExtension($csrfService));
        return $twig;

    }//end initialize()


}//end class
