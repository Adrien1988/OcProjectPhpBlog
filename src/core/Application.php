<?php

namespace App\Core;

use Twig\Environment;
use App\Init\ConfigInit;
use App\Init\ContainerInit;
use App\Init\EnvironmentInit;
use App\Init\ServicesInit;
use App\Handlers\RequestHandler;
use App\Init\TwigInit;
use App\Init\UserInit;
use Symfony\Component\HttpFoundation\Request;

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
     */
    public function __construct()
    {
        // Initialiser les composants de base.
        $envInit    = new EnvironmentInit();
        $envService = $envInit->initialize();

        $configInit = new ConfigInit();
        $config     = $configInit->load();

        $containerInit = new ContainerInit();
        $container     = $containerInit->initialize($config);

        // Initialiser les services et dépendances.
        $servicesInit   = new ServicesInit();
        $this->services = $servicesInit->initialize($envService, $container);

        // Configuration de Twig.
        $twigInit   = new TwigInit();
        $this->twig = $twigInit->initialize($this->services['csrfService']);

        // Récupération de l'utilisateur actuel.
        $userInit    = new UserInit();
        $currentUser = $userInit->getCurrentUser(
            $this->services['sessionService'],
            $this->services['usersRepository']
        );
        $this->twig->addGlobal('app', ['user' => $currentUser]);

        // Création de la requête HTTP.
        $this->request = Request::createFromGlobals();

    }//end __construct()


    /**
     * Exécute l'application.
     * Traite la requête HTTP et envoie la réponse correspondante.
     *
     * @return void
     */
    public function run(): void
    {
        // Traitement de la requête et obtention de la réponse.
        $requestHandler = new RequestHandler();
        $response       = $requestHandler->handle($this->services, $this->twig, $this->request);

        // Envoi de la réponse.
        $response->send();

    }//end run()


}//end class
