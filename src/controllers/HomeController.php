<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Twig\Environment;

/**
 * Contrôleur pour la page d'accueil.
 */
class HomeController
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

    }//end __construct()


    /**
     * Affiche la page d'accueil.
     * Cette méthode rend le template 'home/index.html.twig' avec des données dynamiques
     * pour les éléments du portfolio et les modals, et retourne la réponse HTTP correspondante.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function index(): Response
    {
        // Définition des éléments du portfolio.
        $portfolio_items = [
            ['modal_id' => 'portfolioModal1', 'image' => 'assets/img/portfolio/cabin.png'],
            ['modal_id' => 'portfolioModal2', 'image' => 'assets/img/portfolio/cake.png'],
            ['modal_id' => 'portfolioModal3', 'image' => 'assets/img/portfolio/circus.png'],
            ['modal_id' => 'portfolioModal4', 'image' => 'assets/img/portfolio/game.png'],
            ['modal_id' => 'portfolioModal5', 'image' => 'assets/img/portfolio/safe.png'],
            ['modal_id' => 'portfolioModal6', 'image' => 'assets/img/portfolio/submarine.png'],
        ];

        // Définition des modals associés.
        $modals = [
            [
                'id'          => 'portfolioModal1',
                'title'       => 'Log Cabin',
                'image'       => 'assets/img/portfolio/cabin.png',
                'description' => 'Description for Log Cabin...'
            ],
            [
                'id'          => 'portfolioModal2',
                'title'       => 'Tasty Cake',
                'image'       => 'assets/img/portfolio/cake.png',
                'description' => 'Description for Tasty Cake...'
            ],
            [
                'id'          => 'portfolioModal3',
                'title'       => 'Circus Tent',
                'image'       => 'assets/img/portfolio/circus.png',
                'description' => 'Description for Circus Tent...'
            ],
            [
                'id'          => 'portfolioModal4',
                'title'       => 'Controller',
                'image'       => 'assets/img/portfolio/game.png',
                'description' => 'Description for Controller...'
            ],
            [
                'id'          => 'portfolioModal5',
                'title'       => 'Locked Safe',
                'image'       => 'assets/img/portfolio/safe.png',
                'description' => 'Description for Locked Safe...'
            ],
            [
                'id'          => 'portfolioModal6',
                'title'       => 'Submarine',
                'image'       => 'assets/img/portfolio/submarine.png',
                'description' => 'Description for Submarine...'
            ],
        ];

        // Rendu du template avec les données.
        $content = $this->twig->render(
            'home/index.html.twig',
            [
                'portfolio_items' => $portfolio_items,
                'modals'           => $modals,
            ]
        );

        return new Response($content);

    }//end index()


    /**
     * Télécharge le CV en tant que fichier PDF.
     *
     * @return Response La réponse HTTP contenant le fichier PDF.
     */
    public function downloadCv(): Response
    {
        $file = __DIR__.'/../../public/assets/img/CV_Fauquembergue_Adrien.pdf';

        return new Response(
            file_get_contents($file),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="CV_Fauquembergue_Adrien.pdf"',
            ]
        );

    }//end downloadCv()


}//end class
