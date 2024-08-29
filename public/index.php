<?php

session_start();

use Dotenv\Dotenv;
use App\Models\Post;
use App\Models\User;
use Twig\Environment;
use App\Models\Comment;
use App\Twig\CsrfExtension;
use Models\PostsRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Core\DependencyContainer;
use App\Services\SecurityService;
use Twig\Loader\FilesystemLoader;
use App\Middlewares\CsrfMiddleware;
use App\Controllers\ErrorController;
use App\Controllers\FormsController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Config\Definition\Exception\Exception;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/**
 * Charge la configuration de l'application.
 *
 * @return array La configuration de l'application.
 *
 * @throws Exception Si la configuration de la base de données est introuvable.
 */
function loadConfig(): array
{
    $configPath = __DIR__.'/../src/config/config.php';

    if (file_exists($configPath) === false) {
        throw new Exception('Le fichier de configuration n\'existe pas.');
    }

    // Inclure le fichier de configuration et appeler la fonction getDatabaseConfig.
    include $configPath;
    $config = getDatabaseConfig();

    if ($config === false || isset($config['database']) === false) {
        throw new Exception('Configuration de la base de données introuvable.');
    }

    return $config;

}//end loadConfig()


/**
 * Initialise le conteneur d'injection de dépendances.
 *
 * @param array $config La configuration de l'application.
 *
 * @return DependencyContainer Le conteneur de dépendances initialisé.
 */
function initializeContainer(array $config): DependencyContainer
{
    return new DependencyContainer(
        [
            'dsn'         => 'mysql:host='.$config['database']['host'].';dbname='.$config['database']['dbname'].';charset=utf8mb4',
            'db_user'     => $config['database']['user'],
            'db_password' => $config['database']['password'],
        ]
    );

}//end initializeContainer()


/**
 * Fonction pour gérer les middlewares.
 *
 * @param Request  $request          La requête.
 * @param array    $middlewares      Les middlewares à exécuter.
 * @param callable $controllerAction L'action du contrôleur à exécuter.
 * @param array    $dependencies     Les dépendances à passer aux middlewares.
 *
 * @return Response La réponse générée.
 */
function handleMiddlewares(Request $request, array $middlewares, callable $controllerAction, array $dependencies): Response
{
    $middleware = array_shift($middlewares);

    if ($middleware === null) {
        return $controllerAction($request);
    }

    return $middleware->handle(
        $request,
        function (Request $request) use ($middlewares, $controllerAction, $dependencies) {
            return handleMiddlewares($request, $middlewares, $controllerAction, $dependencies);
        }
    );

}//end handleMiddlewares()


// Inclusion des fichiers nécessaires après les déclarations de fonctions.
require __DIR__.'/../vendor/autoload.php';

// Logique d'exécution après les déclarations et inclusions.
try {
    // Charger la configuration.
    $config = loadConfig();

    // Initialiser le conteneur de dépendances.
    $container = initializeContainer($config);

    // Création des modèles et injection de l'instance de base de données à partir du conteneur.
    $postModel    = new Post($container->getDatabase());
    $userModel    = new User($container->getDatabase());
    $commentModel = new Comment($container->getDatabase());

    // Création de l'instance de PostsRepository.
    $postsRepository = new PostsRepository($container->getDatabase());

    // Configurer Twig.
    $loader = new FilesystemLoader(__DIR__.'/../templates');
    $twig   = new Environment(
        $loader,
        [
            'cache' => false,
            'auto_reload' => true,
        ]
    );

    // Enregistrer l'extension CsrfExtension.
    $csrfService = new CsrfService();
    $twig->addExtension(new CsrfExtension($csrfService));

    // Créez une instance de SecurityService.
    $securityService = new SecurityService();

    // Créez une instance de Dotenv.
    $dotenv = Dotenv::createImmutable(__DIR__.'/../');

    // Créez une instance de EnvService.
    $envService = new EnvService($dotenv);

    // Créer les instances des contrôleurs spécifiques.
    $formsController = new FormsController($securityService, $envService, $csrfService);

    // $errorController = new ErrorController();
    // Charger les routes.
    $routes = include __DIR__.'/../src/config/routes.php';

    // Initialiser le contexte de la requête.
    $context = new RequestContext();
    $request = Request::createFromGlobals();
    $context->fromRequest($request);

    // Initialiser le matcher et le générateur d'URL.
    $matcher   = new UrlMatcher($routes, $context);
    $generator = new UrlGenerator($routes, $context);


    // Try {
    // Matcher la requête à une route.
    // $parameters = $matcher->match($request->getPathInfo());
    // } catch (\Throwable $th) {
    // $class = 'App\Controllers\ErrorController';
    // $parameters['_controller'] = 'App\Controllers\ErrorController';
    // $parameters['_route'] = '/index';
    // }
    // Ajouter un try catch de parameters ici pour intégrer les erreurs d'url dans une page 404.
    $parameters = $matcher->match($request->getPathInfo());
    // Extraire le contrôleur et l'action.
    $controller           = $parameters['_controller'];
    list($class, $method) = explode('::', $controller);

    // Instancier le contrôleur et appeler l'action.
    // var_dump($class, $parameters);
    // die();.
    switch ($class) {
    case 'App\Controllers\FormsController':
        $controllerInstance = $formsController;
        break;

    default:
        $controllerInstance = new $class($twig);
        break;
    }

    // Supprimer les clés réservées de paramètres comme '_controller'.
    unset($parameters['_controller']);

    // Dépendances pour les middlewares.
    $dependencies = [
        'csrfService' => $csrfService,
    ];

    // Middleware à exécuter.
    $middlewares = [
        new CsrfMiddleware($csrfService),
    ];


    // Appeler la méthode du contrôleur avec les middlewares.
    $response = handleMiddlewares(
        $request,
        $middlewares,
        function (Request $request) use ($controllerInstance, $method, $postsRepository, $parameters) {
            return $controllerInstance->$method($request, $postsRepository, ...array_values($parameters));
        },
        $dependencies
    );

    // Envoyer la réponse.
    $response->send();
} catch (Exception $e) {
    // Gestion des erreurs (par exemple, route non trouvée).
    $response = new Response('Not Found: '.$e->getMessage(), 404);
    $response->send();
}//end try
