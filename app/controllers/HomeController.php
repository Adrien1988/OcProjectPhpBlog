<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class HomeController
{

    /**
     * Instance de l'environnement Twig pour le rendu des templates.
     *
     * @var \Twig\Environment
     */
    private $twig;


    /**
     * Constructeur de la classe.
     *
     * Initialise l'instance Twig pour le rendu des templates.
     *
     * @param Environment $twig Instance de l'environnement Twig.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;

    } // end __construct()


    /**
     * Affiche la page d'accueil.
     *
     * Cette méthode rend le template 'home/index.html.twig' avec un message
     * de bienvenue et retourne la réponse HTTP correspondante.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function index()
    {
        $content = $this->twig->render('home/index.html.twig', ['message' => 'Welcome to the home page!']);

        return new Response($content);

    }


}
