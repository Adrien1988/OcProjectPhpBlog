<?php

namespace App\Core;

use Throwable;
use Twig\Environment;
use App\Init\TwigInit;
use App\Init\UserInit;
use App\Init\ConfigInit;
use App\Init\ServicesInit;
use App\Init\ContainerInit;
use App\Init\EnvironmentInit;
use App\Handlers\RequestHandler;
use App\Controllers\ErrorController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Application
{

    /**
     * Tableau des services initialisés.
     *
     * @var array
     */
    private array $services;

    /**
     * Instance de l'environnement Twig.
     *
     * @var Environment
     */
    private Environment $twig;

    /**
     * Instance de la requête HTTP.
     *
     * @var Request
     */
    private Request $request;


    /**
     * Constructeur de la classe Application.
     * Initialise les composants essentiels de l'application.
     *
     * @param Request $request La requête HTTP à traiter.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;

        // Initialiser les composants de base.
        $envInit    = new EnvironmentInit();
        $envService = $envInit->initialize();

        $configInit = new ConfigInit($envService);
        $config     = $configInit->load();

        $containerInit = new ContainerInit();
        $container     = $containerInit->initialize($config);

        // Initialiser les services et dépendances.
        $servicesInit   = new ServicesInit();
        $this->services = $servicesInit->initialize($envService, $container, $request);

        // Configuration de Twig.
        $twigInit   = new TwigInit();
        $this->twig = $twigInit->initialize($this->services['csrfService']);

        // Récupération de l'utilisateur actuel.
        $userInit    = new UserInit();
        $currentUser = $userInit->getCurrentUser(
            $this->services['sessionService'],
            $this->services['usersRepository']
        );
        $this->twig->addGlobal('app', ['user' => $currentUser, 'environment' => $envService->getEnv('APP_ENV', 'prod'), ]);

    }//end __construct()


    /**
     * Exécute l'application.
     * Traite la requête HTTP et envoie la réponse correspondante.
     *
     * @return void
     */
    public function run(): void
    {
        try {
            // Traitement de la requête et obtention de la réponse.
            $requestHandler = new RequestHandler();
            $response       = $requestHandler->handle($this->services, $this->twig, $this->request);

            // Envoi de la réponse.
            $response->send();
        } catch (ResourceNotFoundException $e) {
            $this->handleError(404);
        } catch (Exception $e) {
            $this->handleError(500, $e);
        } catch (Throwable $e) {
            $this->handleError(500, $e);
        }

    }//end run()


    /**
     * Gère les erreurs HTTP et affiche les pages d'erreur correspondantes.
     *
     * @param int            $code      Code HTTP de l'erreur (400, 403, 404, 500).
     * @param Throwable|null $exception (Optionnel) Exception qui a déclenché l'erreur.
     *
     * @return void
     */
    private function handleError(int $code, ?Throwable $exception=null): void
    {
        $errorController = new ErrorController(
            $this->twig,
            $this->services['securityService'],
            $this->services['envService'],
            $this->services['csrfService'],
            $this->services['sessionService'],
            $this->services['emailService'],
            $this->services['validator'],
            $this->services['urlGeneratorService']
        );

        // Valider le code HTTP.
        $httpCode = ($code >= 100 && $code < 600) ? $code : 500;

        // Inclure le message d'erreur dans la page uniquement en mode développement.
        $isDevMode    = $this->services['envService']->getEnv('APP_ENV', 'prod') === 'dev';
        $errorMessage = ($isDevMode === true && $exception !== null) ? $exception->getMessage() : null;

        $response = $errorController->handle($httpCode, $errorMessage);
        $response->send();

    }//end handleError()


}//end class
