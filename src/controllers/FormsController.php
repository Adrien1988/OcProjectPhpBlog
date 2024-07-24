<?php

namespace App\Controllers;

use App\Services\EnvService;
use App\Services\SecurityService;
use App\Services\CsrfService;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FormsController
{

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
     *
     * @param SecurityService $securityService Le service de sécurité pour la protection contre les attaques XSS.
     * @param EnvService      $envService      Instance du service de gestion des variables d'environnement.
     * @param CsrfService     $csrfService     Service pour la gestion des tokens CSRF.
     */
    public function __construct(SecurityService $securityService, EnvService $envService, CsrfService $csrfService)
    {
        $this->securityService = $securityService;
        $this->envService      = $envService;
        $this->csrfService     = $csrfService;

    }//end __construct()


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
        // Charger les variables d'environnement.
        $this->envService->loadEnv(__DIR__.'/../../');

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
        if (empty($name) || empty($email) || empty($message)) {
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
