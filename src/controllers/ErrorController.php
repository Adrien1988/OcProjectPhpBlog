<?php

namespace App\Controllers;

use Twig\Environment;
use Models\PostsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur pour la page d'accueil.
 */
class ErrorController
{

    /**
     * Instance de l'environnement Twig pour le rendu des templates.
     *
     * @var Environment
     */
    private $twig;


    /**
     * Constructeur de la classe.
     * Initialise l'instance Twig pour le rendu des templates.
     *
     * @param Environment $twig Instance de l'environnement Twig.
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    } //end __construct()


    /**
     * Affiche la page d'accueil.
     * Cette méthode rend le template 'home/index.html.twig' avec des données dynamiques
     * pour les éléments du portfolio et les modals, et retourne la réponse HTTP correspondante.
     *
     * @param Request         $request         La requête
     *                                         HTTP courante.

     * @param PostsRepository $postsRepository Le repository des posts pour récupérer les derniers articles.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function index(): Response
    {
        return new Response("test");
    } //end index()



}//end class
