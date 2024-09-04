<?php

namespace App\Controllers;

use Twig\Environment;
use Models\PostsRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\SecurityService;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
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
     * Service de sécurité pour la protection contre les attaques XSS.
     *
     * @var SecurityService
     */
    private $securityService;

    /**
     * Service pour charger les variables d'environnement.
     *
     * @var EnvService
     */
    private EnvService $envService;

    /**
     * Service pour la gestion des tokens CSRF.
     *
     * @var CsrfService
     */
    private $csrfService;


    /**
     * Constructeur de la classe.
     * Initialise l'instance Twig pour le rendu des templates.
     *
     * @param Environment     $twig            Instance de l'environnement Twig.
     * @param SecurityService $securityService Le service de sécurité pour la protection contre les attaques XSS.
     * @param EnvService      $envService      Instance du service de gestion des variables d'environnement.
     * @param CsrfService     $csrfService     Service pour la gestion des tokens CSRF.
     */
    public function __construct(Environment $twig, SecurityService $securityService, EnvService $envService, CsrfService $csrfService)
    {
        $this->twig            = $twig;
        $this->securityService = $securityService;
        $this->envService      = $envService;
        $this->csrfService     = $csrfService;

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

        // Utiliser $request de manière inoffensive pour éviter l'avertissement.
        $request->getMethod();
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
        if ($this->csrfService->isTokenValid('contact_form', $submittedToken) === false) {
            return new Response('Invalid CSRF token.', 400);
        }

        // Récupérer les données du formulaire.
        $name    = $this->securityService->cleanInput($request->request->get('name', ''));
        $email   = $this->securityService->cleanInput($request->request->get('email', ''));
        $message = $this->securityService->cleanInput($request->request->get('message', ''));

        // Valider les données.
        if (empty($name) === true || empty($email) === true || empty($message) === true) {
            return new Response('Tous les champs sont requis.', 400);
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new Response('Email non valide.', 400);
        }

        // Vérifiez l'adresse e-mail de destination.
        $toEmail = $this->envService->getEnv('SMTP_USERNAME');

        // Envoyer l'email.
        $mail = new PHPMailer(true);

        try {
            // Configurer le serveur SMTP.
            $mail->isSMTP();
            $mail->Host       = $this->envService->getEnv('SMTP_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->envService->getEnv('SMTP_USERNAME');
            $mail->Password   = $this->envService->getEnv('SMTP_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $this->envService->getEnv('SMTP_PORT', 587);

            // Destinataires.
            $mail->setFrom($email, $name);
            $mail->addAddress($toEmail);

            // Contenu de l'email.
            $mail->isHTML(true);
            $mail->Subject = 'Nouveau message de contact';
            $mail->Body    = '<b>Nom:</b> '.$name.'<br><b>Email:</b> '.$email.'<br><b>Message:</b><br>'.nl2br($message);

            $mail->send();

            return new Response('Message envoyé avec succès!', 200);
        } catch (Exception $e) {
            return new Response('Erreur lors de l\'envoi du message: '.$e->getMessage(), 500);
        }//end try

    }//end submitContact()


}//end class
