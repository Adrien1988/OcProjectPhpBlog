<?php

namespace App\Services;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class EmailService
{

    /**
     * Instance de PHPMailer utilisée pour l'envoi d'e-mails.
     *
     * @var PHPMailer
     */
    private PHPMailer $mailer;

    /**
     * Service pour accéder aux variables d'environnement.
     *
     * @var EnvService
     */
    private EnvService $envService;


    /**
     * Constructeur de la classe EmailService.
     *
     * Initialise l'instance de PHPMailer et configure les paramètres SMTP.
     *
     * @param EnvService $envService Service pour accéder aux variables d'environnement.
     *
     * @throws Exception Si une erreur survient lors de la configuration de PHPMailer.
     */
    public function __construct(EnvService $envService)
    {
        $this->envService = $envService;
        $this->mailer     = new PHPMailer(true);

        // Configuration du serveur SMTP.
        $this->mailer->isSMTP();
        $this->mailer->Host       = $this->envService->getEnv('SMTP_HOST', 'smtp.gmail.com');
        $this->mailer->SMTPAuth   = true;
        $this->mailer->Username   = $this->envService->getEnv('SMTP_USERNAME');
        $this->mailer->Password   = $this->envService->getEnv('SMTP_PASSWORD');
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port       = $this->envService->getEnv('SMTP_PORT', 587);

         // Définir l'expéditeur par défaut.
         $defaultFromEmail = $this->envService->getEnv('SMTP_FROM_EMAIL', $this->mailer->Username);
         $defaultFromName  = $this->envService->getEnv('SMTP_FROM_NAME', 'Votre Application');

        $this->mailer->setFrom($defaultFromEmail, $defaultFromName);

    }//end __construct()


    /**
     * Envoie un e-mail.
     *
     * @param string      $to      L'adresse e-mail du destinataire.
     * @param string      $subject Le sujet de l'e-mail.
     * @param string      $body    Le corps de l'e-mail (HTML autorisé).
     * @param string|null $from    (Optionnel) L'adresse e-mail de l'expéditeur.
     * @param string|null $name    (Optionnel) Le nom de l'expéditeur.
     *
     * @return void
     *
     * @throws Exception Si l'envoi de l'e-mail échoue.
     */
    public function sendEmail(string $to, string $subject, string $body, ?string $from=null, ?string $name=null): void
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Définir l'expéditeur si spécifié.
            if ($from !== null) {
                $this->mailer->setFrom($from, ($name ?? $from));
            } else {
                // Réinitialiser l'expéditeur au cas où il aurait été modifié précédemment.
                $defaultFromEmail = $this->envService->getEnv('SMTP_FROM_EMAIL', $this->mailer->Username);
                $defaultFromName  = $this->envService->getEnv('SMTP_FROM_NAME', 'Votre Application');
                $this->mailer->setFrom($defaultFromEmail, $defaultFromName);
            }

            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body    = $body;
            $this->mailer->isHTML(true);

            $this->mailer->send();
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'envoi de l\'e-mail : '.$e->getMessage());
        }//end try

    }//end sendEmail()


}//end class
