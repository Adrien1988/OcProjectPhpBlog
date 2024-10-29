<?php

use Dotenv\Dotenv;
use Twig\Environment;
use App\Twig\CsrfExtension;
use Models\PostsRepository;
use Models\UsersRepository;
use App\Models\User;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\EmailService;
use Models\CommentsRepository;
use App\Services\SessionService;
use App\Core\DependencyContainer;
use App\Services\SecurityService;
use Twig\Loader\FilesystemLoader;
use App\Middlewares\CsrfMiddleware;
use App\Services\UrlGeneratorService;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

// Inclusion de l'autoloader
require __DIR__ . '/../vendor/autoload.php';

try {
    // Initialiser les composants de base
    $envService = initializeEnvironment();
    $config = loadConfig();
    $container = initializeContainer($config);
    $validator = Validation::createValidator();

    // Initialiser les services et dépendances
    $services = initializeServices($envService, $container, $validator);

    // Configuration Twig et récupération de l'utilisateur
    $twig = initializeTwig($services['csrfService']);
    $currentUser = getCurrentUser($services['sessionService'], $services['usersRepository']);
    $twig->addGlobal('app', ['user' => $currentUser]);

    // Initialiser le contexte, les routes et le générateur d'URL
    $context = new RequestContext();
    $request = Request::createFromGlobals();
    $context->fromRequest($request);
    $routes = include __DIR__ . '/../src/config/routes.php';
    $matcher = new UrlMatcher($routes, $context);
    $urlGeneratorService = new UrlGeneratorService(new UrlGenerator($routes, $context));

    // Correspondance de la route
    $parameters = $matcher->match($request->getPathInfo());

    // **Extraction du contrôleur et de la méthode de la route**
    if (isset($parameters['_controller']) === false) {
        throw new Exception('Le contrôleur n\'est pas défini dans les paramètres de la route.');
    }

    $controller = $parameters['_controller'];
    list($class, $method) = explode('::', $controller);

    // **Vérification que la méthode n'est pas nulle**
    if (empty($method)) {
        throw new Exception('La méthode est indéfinie ou vide.');
    }

    // Suppression des clés réservées avant de passer les paramètres
    unset($parameters['_controller'], $parameters['_route']);

    // Instanciation du contrôleur
    $controllerInstance = getControllerInstance($class, $twig, $services, $urlGeneratorService);

    // Exécution des middlewares et de l'action du contrôleur
    // Exécution des middlewares et de l'action du contrôleur
    $middlewares = $request->isMethod('POST') ? [new CsrfMiddleware($services['csrfService'])] : [];
    $response = handleMiddlewares(
        $request,
        $middlewares,
        fn() => executeControllerAction($controllerInstance, $method, $parameters, $request, $services) // Utilisation de $method
    );
} catch (Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
    $response = new Response('Page non trouvée : ' . $e->getMessage(), 404);
} catch (Exception $e) {
    $response = new Response('Une erreur est survenue : ' . $e->getMessage(), 500);
}

// Envoi de la réponse
$response->send();

/**
 * Initialisation de l'environnement.
 */
function initializeEnvironment(): EnvService
{
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    return new EnvService($dotenv);
}

/**
 * Charge la configuration de l'application.
 */
function loadConfig(): array
{
    $configPath = __DIR__ . '/../src/config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('Le fichier de configuration n\'existe pas.');
    }

    include $configPath;
    $config = getDatabaseConfig();

    if ($config === false || !isset($config['database'])) {
        throw new Exception('Configuration de la base de données introuvable.');
    }

    return $config;
}

/**
 * Initialise le conteneur de dépendances.
 */
function initializeContainer(array $config): DependencyContainer
{
    return new DependencyContainer([
        'dsn' => 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['dbname'] . ';charset=utf8mb4',
        'db_user' => $config['database']['user'],
        'db_password' => $config['database']['password'],
    ]);
}

/**
 * Initialise les services principaux.
 */
function initializeServices(EnvService $envService, DependencyContainer $container, $validator): array
{
    $csrfService = new CsrfService();
    $sessionService = initializeSessionService();
    return [
        'csrfService' => $csrfService,
        'securityService' => new SecurityService(),
        'emailService' => new EmailService($envService),
        'envService' => $envService,
        'sessionService' => $sessionService,
        'postsRepository' => new PostsRepository($container->getDatabase(), $validator),
        'usersRepository' => new UsersRepository($container->getDatabase(), $validator),
        'commentsRepository' => new CommentsRepository($container->getDatabase()),
        'validator' => $validator
    ];
}

/**
 * Initialisation de Twig avec l'extension CSRF.
 */
function initializeTwig(CsrfService $csrfService): Environment
{
    $loader = new FilesystemLoader(__DIR__ . '/../templates');
    $twig = new Environment($loader, ['cache' => false, 'auto_reload' => true]);
    $twig->addExtension(new CsrfExtension($csrfService));
    return $twig;
}

/**
 * Initialisation du service de session Symfony.
 */
function initializeSessionService(): SessionService
{
    $sessionStorage = new NativeSessionStorage();
    $session = new Session($sessionStorage);
    $session->start();
    return new SessionService($session);
}

/**
 * Récupère l'utilisateur actuellement connecté.
 */
function getCurrentUser(SessionService $sessionService, UsersRepository $usersRepository): ?User
{
    if ($sessionService->has('user_id')) {
        $userId = $sessionService->get('user_id');
        return $usersRepository->findById($userId);
    }
    return null;
}

/**
 * Instancie le contrôleur en fonction de la route.
 */
function getControllerInstance($class, Environment $twig, $services, UrlGeneratorService $urlGeneratorService)
{
    return new $class(
        $twig,
        $services['securityService'],
        $services['envService'],
        $services['csrfService'],
        $services['sessionService'],
        $services['emailService'],
        $services['validator'],
        $urlGeneratorService
    );
}

/**
 * Exécute une action du contrôleur avec les paramètres appropriés.
 */
function executeControllerAction($controllerInstance, string $method, array $parameters, Request $request, array $services): Response
{
    try {
        $reflectionMethod = new \ReflectionMethod($controllerInstance, $method);
        if ($reflectionMethod->isPublic() === false) {
            throw new Exception('Méthode non accessible');
        }

        $methodParameters = [];
        foreach ($reflectionMethod->getParameters() as $param) {
            $paramType = $param->getType();
            $paramName = $param->getName();

            $methodParameters[] = match ($paramType?->getName()) {
                Request::class => $request,
                PostsRepository::class => $services['postsRepository'],
                UsersRepository::class => $services['usersRepository'],
                default => $parameters[$paramName] ?? null,
            };
        }

        return $controllerInstance->$method(...$methodParameters);
    } catch (\ReflectionException $e) {
        return new Response('Méthode introuvable : ' . $e->getMessage(), 404);
    } catch (Exception $e) {
        return new Response('Une erreur est survenue : ' . $e->getMessage(), 500);
    }
}

/**
 * Gère l'exécution des middlewares.
 */
function handleMiddlewares(Request $request, array $middlewares, callable $controllerAction): Response
{
    $middleware = array_shift($middlewares);
    return $middleware === null
        ? $controllerAction($request)
        : $middleware->handle($request, fn($req) => handleMiddlewares($req, $middlewares, $controllerAction));
}
