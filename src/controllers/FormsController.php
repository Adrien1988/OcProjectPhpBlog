<?php

namespace App\Controllers;

use Dotenv\Dotenv;
use App\Services\SecurityService;
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
     * Constructeur de la classe.
     *
     * @param SecurityService $securityService Le service de sécurité pour la protection contre les attaques XSS.
     */
    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;

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
        // Récupérer les données du formulaire.
        $name    = $this->securityService->cleanInput($request->request->get('name'));
        $email   = $this->securityService->cleanInput($request->request->get('email'));
        $message = $this->securityService->cleanInput($request->request->get('message'));

        // Valider les données.
        if (empty($name) === true || empty($email) === true || empty($message) === true) {
            return new Response('Tous les champs sont requis.', 400);
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new Response('Email non valide.', 400);
        }

        // Charger les variables d'environnement.
        $dotenv = Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        // Envoyer l'email.
        $mail = new PHPMailer(true);

        try {
            // Configurer le serveur SMTP.
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Destinataires.
            $mail->setFrom($email, $name);
            $mail->addAddress($_ENV['SMTP_USERNAME']);

            // Contenu de l'email.
            $mail->isHTML(true);
            $mail->Subject = 'Nouveau message de contact';
            $mail->Body    = '<b>Nom:</b> '.$name.'<br><b>Email:</b> '.$email.'<br><b>Message:</b><br>'.nl2br($message);

            $mail->send();

            return new Response('Message envoyé avec succès!', 200);
        } catch (Exception $e) {
            return new Response('Erreur lors de l\'envoi du message: '.$mail->ErrorInfo, 500);
        }//end try

    }//end submitContact()


}//end class
