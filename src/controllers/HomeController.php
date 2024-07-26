<?php

namespace App\Controllers;

use Dotenv\Dotenv;
use Twig\Environment;
use Models\PostsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Request         $request         La requête HTTP courante.
     * @param PostsRepository $postsRepository Le repository des posts pour récupérer les derniers articles.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function index(Request $request, PostsRepository $postsRepository): Response
    {
        // Définition des éléments du portfolio.
        $portfolioItems = [
            ['modal_id' => 'portfolioModal1', 'image' => 'assets/img/portfolio/pageAcceuilWordpress.png'],
            ['modal_id' => 'portfolioModal2', 'image' => 'assets/img/portfolio/pageAccueilFilmsPleinAir.png'],
            ['modal_id' => 'portfolioModal3', 'image' => 'assets/img/portfolio/ExpressFood_Delivery_Cyclist.png'],
        ];

        // Définition des modals associés.
        $modals = [
            [
                'id'          => 'portfolioModal1',
                'title'       => 'Intégrez un thème Wordpress',
                'image'       => 'assets/img/portfolio/pageAcceuilWordpress.png',
                'description' => 'Projet fictif de réalisation d\'un site web en utilisant le CMS Wordpress. <a href="https://www.chaletscaviar.fr" target="_blank">Visitez le site</a>'
            ],
            [
                'id'          => 'portfolioModal2',
                'title'       => 'Analyser les besoins de votre client pour son festival de films',
                'image'       => 'assets/img/portfolio/pageAccueilFilmsPleinAir.png',
                'description' => 'Projet fictif de réalisation d\'une solution digitale de communication pour une association. <a href="https://www.films-de-plein-air.org" target="_blank">Visitez le site</a>'
            ],
            [
                'id'          => 'portfolioModal3',
                'title'       => 'Concevoir la solution technique d\'une application de restauration en ligne',
                'image'       => 'assets/img/portfolio/ExpressFood_Delivery_Cyclist.png',
                'description' => 'Projet fictif de réalisation d\'une solution technique pour une application de livraison de plats à domicile. Vous pouvez consulter les fichiers et diagrammes du projet via ce lien : <a href="https://drive.google.com/drive/folders/1r3lekSG3pgmx838T0LIU5xyGnhYQuPfl?usp=sharing">Consulter le dossier</a>'
            ],
        ];

        $posts = $postsRepository->findLatest();

        // Rendu du template avec les données.
        $content = $this->twig->render(
            'home/index.html.twig',
            [
                'portfolioItems' => $portfolioItems,
                'modals'           => $modals,
                'posts' => $posts,
            ]
        );

        return new Response($content);

    }//end index()


    /**
     * Affiche la page des conditions d'utilisation.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function showTerms(): Response
    {
        $content = $this->twig->render('legal/termsOfService.html.twig');

        return new Response($content);

    }//end showTerms()


    /**
     * Affiche la page de politique de confidentialité.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function showPrivacyPolicy(): Response
    {
        $content = $this->twig->render('legal/privacyPolicy.html.twig');
        return new Response($content);

    }//end showPrivacyPolicy()


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
