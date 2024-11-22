<?php

namespace App\Controllers;

use Twig\Environment;
use Models\PostsRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\EmailService;
use App\Services\SessionService;
use App\Services\SecurityService;
use App\Services\UrlGeneratorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;




class BaseController
{

    /**
     * Instance de l'environnement Twig pour le rendu des templates.
     *
     * @var Environment
     */
    protected $twig;

    /**
     * Service de sécurité pour la protection contre les attaques XSS.
     *
     * @var SecurityService
     */
    protected $securityService;

    /**
     * Service pour charger les variables d'environnement.
     *
     * @var EnvService
     */
    protected EnvService $envService;

    /**
     * Service pour la gestion des tokens CSRF.
     *
     * @var CsrfService
     */
    protected $csrfService;

    /**
     * Service de session pour gérer les sessions utilisateur.
     *
     * @var SessionService
     */
    protected SessionService $sessionService;

    /**
     * Service pour la gestion des envois de mail.
     *
     * @var EmailService
     */
    protected EmailService $emailService;

    /**
     * Le validateur Symfony pour la validation des entités.
     *
     * @var ValidatorInterface
     */
    protected ValidatorInterface $validator;

    /**
     * Service de génération d'URL.
     *
     * @var UrlGeneratorService
     */
    protected UrlGeneratorService $urlGeneratorService;


    /**
     * Constructeur de la classe.
     * Initialise l'instance Twig pour le rendu des templates.
     *
     * @param Environment         $twig                Instance de l'environnement Twig.
     * @param SecurityService     $securityService     Le service de sécurité pour la protection contre les
     *                                                 attaques XSS.
     * @param EnvService          $envService          Instance du service de gestion des variables d'environnement.
     * @param CsrfService         $csrfService         Service pour la gestion des tokens CSRF.
     * @param SessionService      $sessionService      L'instance de SessionService pour la gestion des sessions.
     * @param EmailService        $emailService        Service pour l'envoi de mail.
     * @param ValidatorInterface  $validator           Le validateur Symfony pour la validation des
     *                                                 entités.
     * @param UrlGeneratorService $urlGeneratorService Service pour générer les URLs de base.
     */
    public function __construct(
        Environment $twig,
        SecurityService $securityService,
        EnvService $envService,
        CsrfService $csrfService,
        SessionService $sessionService,
        EmailService $emailService,
        ValidatorInterface $validator,
        UrlGeneratorService $urlGeneratorService
    ) {
        $this->twig            = $twig;
        $this->securityService = $securityService;
        $this->envService      = $envService;
        $this->csrfService     = $csrfService;
        $this->sessionService  = $sessionService;
        $this->emailService    = $emailService;
        $this->validator       = $validator;
        $this->urlGeneratorService = $urlGeneratorService;

        if ($this->sessionService->isStarted() === false) {
            $this->sessionService->start();
        }

    }//end __construct()


    // Méthodes de rendu.


    /**
     * Rendre un template Twig avec les données fournies.
     *
     * @param string $template Le nom du template.
     * @param array  $data     Les données à passer
     *                         au
     *
     * @return Response
     */
    protected function render(string $template, array $data=[]): Response
    {
        $content = $this->twig->render($template, $data);
        return new Response($content);

    }//end render()


    /**
     * Rends un template Twig en une chaîne de caractères.
     *
     * @param string $template Le chemin du template Twig, relatif au dossier des templates.
     * @param array  $data     Les données à passer au
     *                         template.
     *
     * @return string Le contenu rendu du template.
     */
    protected function renderTemplate(string $template, array $data=[]): string
    {
        return $this->twig->render($template, $data);

    }//end renderTemplate()


    /**
     * Rend une page d'erreur avec un message et un code de statut.
     *
     * @param string          $message    Le message d'erreur à
     *                                    afficher.
     * @param int             $statusCode Le code de statut HTTP à
     *                                    retourner.
     * @param \Exception|null $exception  (Optionnel) L'exception qui a déclenché
     *                                    l'erreur.
     *
     * @return Response La réponse HTTP avec le message d'erreur.
     */
    protected function renderError(string $message, int $statusCode=500, ?\Exception $exception=null): Response
    {
        // Si une exception est fournie, on peut consigner l'erreur dans un fichier de log.
        if ($exception !== null) {
            error_log('[Error] '.$exception->getMessage().' in '.$exception->getFile().' on line '.$exception->getLine());
        }

        // Rendu d'une page d'erreur Twig (si disponible) ou simple réponse texte.
        try {
            return $this->render(
                'error.html.twig',
                [
                    'error_message' => $message,
                    'status_code' => $statusCode,
                    'exception' => $exception,
                ]
            )->setStatusCode($statusCode);
        } catch (\Exception $e) {
            // Si le rendu Twig échoue, consigner l'erreur dans les logs et retourner une réponse texte.
            error_log('[Error Rendering] '.$e->getMessage().' in '.$e->getFile().' on line '.$e->getLine());
            return new Response($message.' (Une erreur supplémentaire est survenue lors du rendu de la page d\'erreur.)', $statusCode);
        }

    }//end renderError()


    // Méthodes de validation CSRF.


    /**
     * Générer un token CSRF.
     *
     * @param string $tokenId L'identifiant du token.
     *
     * @return string
     */
    protected function generateCsrfToken(string $tokenId): string
    {
        return $this->csrfService->generateToken($tokenId);

    }//end generateCsrfToken()


    /**
     * Méthode pour valider les tokens CSRF.
     *
     * @param string $tokenId        L'identifiant du token.
     * @param string $submittedToken Le token soumis.
     *
     * @return bool
     */
    protected function isCsrfTokenValid(string $tokenId, string $submittedToken): bool
    {
        return $this->csrfService->isTokenValid($tokenId, $submittedToken);

    }//end isCsrfTokenValid()


    /**
     * Valide un token CSRF ou lève une exception en cas d'échec.
     *
     * @param string  $tokenId Le nom du token attendu.
     * @param Request $request La requête contenant le token soumis.
     *
     * @return void
     *
     * @throws Exception Si le token CSRF est invalide.
     */
    protected function isCsrfTokenValidOrFail(string $tokenId, Request $request): void
    {
        $submittedToken = $request->request->get('_csrf_token');
        if ($this->isCsrfTokenValid($tokenId, $submittedToken) === false) {
            throw new Exception('Invalid CSRF token.', 403);
        }

    }//end isCsrfTokenValidOrFail()


    /**
     * Gérer une erreur CSRF.
     *
     * @return Response
     */
    protected function csrfErrorResponse(): Response
    {
        return new Response('Invalid CSRF token.', 403);

    }//end csrfErrorResponse()


    // Nettoyage des entrées utilisateur.


    /**
     * Méthode pour nettoyer les entrées utilisateur avec le service de sécurité.
     *
     * @param string $input L'entrée à nettoyer.
     *
     * @return string
     */
    protected function cleanInput(string $input): string
    {
        return $this->securityService->cleanInput($input);

    }//end cleanInput()


    // Validation d'entité.


    /**
     * Valide une entité et retourne les erreurs.
     *
     * @param object $entity L'entité à valider.
     *
     * @return array Liste des messages d'erreur.
     */
    protected function validateEntity(object $entity): array
    {
        $violations = $this->validator->validate($entity);
        $errors     = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }

        return $errors;

    }//end validateEntity()


    /**
     * Gère les erreurs de validation.
     *
     * @param string      $template  Le template à afficher.
     * @param array       $errors    Liste des erreurs.
     * @param array       $data      Données supplémentaires à transmettre.
     * @param string|null $csrfToken Token CSRF.
     *
     * @return Response
     */
    protected function renderFormWithErrors(string $template, array $errors, array $data, ?string $csrfToken): Response
    {
        $context = array_merge(['errors' => $errors, 'csrf_token' => $csrfToken], $data);
        return $this->render($template, $context);

    }//end renderFormWithErrors()


    // Gestion des variables d'environnement.


    /**
     * Méthode pour obtenir une variable d'environnement.
     *
     * @param string      $key     La clé de la
     *                             variable
     * @param string|null $default La valeur par défaut si la clé n'est pas définie.
     *
     * @return string|null
     */
    protected function getEnv(string $key, ?string $default=null): ?string
    {
        return $this->envService->getEnv($key, $default);

    }//end getEnv()


    /**
     * Récupère une valeur de la session.
     *
     * @param string $key     La clé de la valeur à récupérer.
     * @param mixed  $default La valeur par défaut si la clé n'existe pas.
     *
     * @return mixed La valeur de la session ou la valeur par défaut.
     */
    protected function getSessionValue(string $key, $default=null)
    {
        return $this->sessionService->get($key, $default);

    }//end getSessionValue()


    /**
     * Définit une valeur dans la session.
     *
     * @param string $key   La clé de la valeur à définir.
     * @param mixed  $value La valeur à définir.
     *
     * @return void
     */
    protected function setSessionValue(string $key, $value): void
    {
        $this->sessionService->set($key, $value);

    }//end setSessionValue()


    /**
     * Supprime une valeur de la session.
     *
     * @param string $key La clé de la valeur à supprimer.
     *
     * @return void
     */
    protected function removeSessionValue(string $key): void
    {
        $this->sessionService->remove($key);

    }//end removeSessionValue()


    /**
     * Vérifie si une clé existe dans la session.
     *
     * @param string $key La clé à vérifier.
     *
     * @return bool True si la clé existe, false sinon.
     */
    protected function hasSessionKey(string $key): bool
    {
        return $this->sessionService->has($key);

    }//end hasSessionKey()


    // Messages de succès dans la session.


    /**
     * Gère les messages de succès dans la session.
     *
     * @param string $message Le message de succès à stocker.
     *
     * @return void
     */
    protected function setSuccessMessage(string $message): void
    {
        $this->sessionService->set('success_message', $message);

    }//end setSuccessMessage()


    /**
     * Récupère et supprime un message de succès dans la session.
     *
     * @return string|null Le message de succès ou null si aucun message n'existe.
     */
    protected function getAndRemoveSuccessMessage(): ?string
    {
        $message = $this->sessionService->get('success_message');
        $this->sessionService->remove('success_message');
        return $message;

    }//end getAndRemoveSuccessMessage()


    // Méthodes utilitaires.


    /**
     * Vérifie si la requête est de type POST.
     *
     * @param Request $request La requête HTTP.
     *
     * @return bool True si la requête est de type POST, sinon False.
     */
    protected function isPostRequest(Request $request): bool
    {
        return $request->isMethod('POST');

    }//end isPostRequest()


    // /**
    // * Valide l'URL de redirection pour s'assurer qu'elle est sûre.
    // *
    // * @param string $url L'URL à valider.
    // *
    // * @return bool True si l'URL est valide, sinon False.
    // */
    // protected function validateRedirectUrl(string $url): bool
    // {
    // Liste des motifs autorisés pour toutes les entités.
    // $allowedPatterns = [
    // Routes pour les posts.
    // '#^/posts$#',
    // '#^/posts/$#',
    // '#^/posts/\d+$#',
    // '#^/posts/create$#',
    // '#^/posts/edit/\d+$#',
    // '#^/posts/delete/\d+$#',
    // Routes pour les comments.
    // '#^/comments$#',
    // '#^/comments/\d+$#',
    // '#^/comments/edit/\d+$#',
    // '#^/comments/delete/\d+$#',
    // Routes pour les users.
    // '#^/users$#',
    // '#^/users/\d+$#',
    // '#^/users/edit/\d+$#',
    // '#^/users/delete/\d+$#',
    // ];
    // Vérifiez si l'URL correspond à l'un des motifs autorisés.
    // foreach ($allowedPatterns as $pattern) {
    // if (preg_match($pattern, $url) === true) {
    // return true;
    // }
    // }
    // return false;
    // }//end validateRedirectUrl()


    /**
     * Redirige vers une URL donnée.
     *
     * @param string $url L'URL vers laquelle rediriger.
     *
     * @return Response
     */
    protected function redirect(string $url): Response
    {

        // Vérifie que l'URL est relative (commence par "/") pour garantir qu'elle reste interne.
        if (str_starts_with($url, '/') === false) {
            throw new Exception('L\'URL de redirection doit être relative et commencer par "/". Donnée : '.$url, 400);
        }

        // Vérifie que l'URL ne contient pas de protocole ou de domaine externe.
        if (preg_match('#^(https?:)?//#', $url) === true) {
            throw new Exception('Les redirections vers des URLs externes ne sont pas autorisées : '.$url, 400);
        }

        // Vérifie que l'URL ne contient pas de caractères interdits.
        if (preg_match('#^[\w\-\/\?=&%]+$#', $url) === false) {
            throw new Exception('L\'URL contient des caractères non autorisés : '.$url, 400);
        }

        return new Response('', 302, ['Location' => $url]);

    }//end redirect()


    /**
     * Récupère un post ou lève une exception s'il est introuvable.
     *
     * @param int             $postId          L'identifiant du post à récupérer.
     * @param PostsRepository $postsRepository Le repository utilisé pour accéder aux posts.
     *
     * @return object Le post trouvé.
     *
     * @throws Exception Si le post n'est pas trouvé.
     */
    protected function fetchPostOrFail(int $postId, PostsRepository $postsRepository): object
    {
        $post = $postsRepository->findById($postId);
        if ($post === null) {
            throw new Exception('Post introuvable.', 404);
        }

        return $post;

    }//end fetchPostOrFail()


    /**
     * Obtient l'instance du validateur Symfony.
     *
     * @return ValidatorInterface
     */
    protected function getValidator(): ValidatorInterface
    {
        return $this->validator;

    }//end getValidator()


}//end class
