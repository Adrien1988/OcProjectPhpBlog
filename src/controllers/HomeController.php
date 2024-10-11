<?php

namespace App\Controllers;


use Models\PostsRepository;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Contrôleur pour la page d'accueil.
 */
class HomeController extends BaseController
{


    /**
     * Affiche la page d'accueil.
     * Cette méthode rend le template 'home/index.html.twig' avec des données dynamiques
     * pour les éléments du portfolio et les modals, et retourne la réponse HTTP correspondante.
     *
     * @param PostsRepository $postsRepository Le repository des posts pour récupérer les derniers articles.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function index(PostsRepository $postsRepository): Response
    {

        // Consomme la variable sans rien en faire.
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
        return $this->render(
            'home/index.html.twig',
            [
                'portfolioItems' => $portfolioItems,
                'modals'           => $modals,
                'posts' => $posts,
            ]
        );

    }//end index()


    /**
     * Affiche la page des conditions d'utilisation.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function showTerms(): Response
    {
        return $this->render('legal/termsOfService.html.twig');

    }//end showTerms()


    /**
     * Affiche la page de politique de confidentialité.
     *
     * @return Response La réponse HTTP contenant le contenu rendu du template.
     */
    public function showPrivacyPolicy(): Response
    {
        return $this->render('legal/privacyPolicy.html.twig');

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


    /**
     * Gère la soumission du formulaire de contact.
     *
     * Cette méthode traite la soumission du formulaire de contact, valide les
     * données saisies et envoie un email à l'aide de PHPMailer.
     *
     * @param Request $request L'instance de la requête courante.
     *
     * @return Response L'instance de la réponse.
     */
    public function submitContact(Request $request): Response
    {

        // Vérifier le token CSRF.
        $submittedToken = $request->request->get('_csrf_token');
        if ($this->isCsrfTokenValid('contact_form', $submittedToken) === false) {
            return new Response('Invalid CSRF token.', 400);
        }

        // Récupérer les données du formulaire.
        $name    = $this->cleanInput($request->request->get('name', ''));
        $email   = $this->cleanInput($request->request->get('email', ''));
        $message = $this->cleanInput($request->request->get('message', ''));

        // Valider les données.
        if (empty($name) === true || empty($email) === true || empty($message) === true) {
            return new Response('Tous les champs sont requis.', 400);
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new Response('Email non valide.', 400);
        }

        try {
            // Vérifiez l'adresse e-mail de destination.
            $toEmail = $this->getEnv('SMTP_USERNAME');

            if (empty($toEmail) === true) {
                return new Response('Adresse e-mail de destination non configurée.', 500);
            }

            // Contenu de l'email.
            $subject = 'Nouveau message de contact';
            $body    = '<b>Nom:</b> '.$name.'<br><b>Email:</b> '.$email.'<br><b>Message:</b><br>'.nl2br($message);

            $this->emailService->sendEmail($toEmail, $subject, $body, $email, $name);

            return new Response('Message envoyé avec succès!', 200);
        } catch (Exception $e) {
            return new Response('Erreur lors de l\'envoi du message: '.$e->getMessage(), 500);
        }//end try

    }//end submitContact()


}//end class
