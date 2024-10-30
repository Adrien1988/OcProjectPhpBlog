<?php

namespace App\Init;

use App\Models\User;
use Twig\Environment;
use Dotenv\Loader\Loader;
use Dotenv\Parser\Parser;
use App\Twig\CsrfExtension;
use Models\PostsRepository;
use Models\UsersRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use App\Services\EmailService;
use Dotenv\Store\StoreBuilder;
use Models\CommentsRepository;
use App\Services\SessionService;
use App\Core\DependencyContainer;
use App\Services\SecurityService;
use Twig\Loader\FilesystemLoader;
use App\Middlewares\CsrfMiddleware;
use App\Services\UrlGeneratorService;
use Dotenv\Repository\RepositoryBuilder;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class Initializer
{


    /**
     * Initialise les variables d'environnement en utilisant Dotenv.
     *
     * @return EnvService Le service d'environnement contenant les variables chargées.
     */
    public static function initializeEnvironment(): EnvService
    {
        $repository = RepositoryBuilder::createWithDefaultAdapters()->make();

        $store = StoreBuilder::createWithNoNames()
            ->addPath(__DIR__.'/../../')
            ->addName('.env')
            ->make();

        $content = $store->read();

        $parser  = new Parser();
        $entries = $parser->parse($content);

        $loader = new Loader();

        foreach ($entries as $entry) {
            $loader->load($repository, [$entry]);
        }

        return new EnvService($repository);

    }//end initializeEnvironment()


    /**
     * Charge la configuration de l'application.
     *
     * @return array La configuration sous forme de tableau.
     *
     * @throws Exception Si le fichier de configuration n'existe pas ou est invalide.
     */
    public static function loadConfig(): array
    {
        $configPath = __DIR__.'/../config/config.php';
        if (file_exists($configPath) === false) {
            throw new Exception('Le fichier de configuration n\'existe pas.');
        }

        include $configPath;
        $config = getDatabaseConfig();

        if ($config === false || isset($config['database']) === false) {
            throw new Exception('Configuration de la base de données introuvable.');
        }

        return $config;

    }//end loadConfig()


    /**
     * Initialise le conteneur de dépendances avec la configuration de la base de données.
     *
     * @param array $config La configuration de l'application.
     *
     * @return DependencyContainer Le conteneur de dépendances initialisé.
     */
    public static function initializeContainer(array $config): DependencyContainer
    {
        return new DependencyContainer(
            [
                'dsn' => 'mysql:host='.$config['database']['host'].';dbname='.$config['database']['dbname'].';charset=utf8mb4',
                'db_user' => $config['database']['user'],
                'db_password' => $config['database']['password'],
            ]
        );

    }//end initializeContainer()


    /**
     * Initialise le traducteur pour une locale donnée.
     *
     * @param string $locale La locale à utiliser pour les traductions (par exemple, 'fr').
     *
     * @return Translator Le traducteur initialisé.
     */
    public static function initializeTranslator(string $locale): Translator
    {
        $translator = new Translator($locale);
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource(
            'xlf',
            __DIR__.'/../../vendor/symfony/validator/Resources/translations/validators.'.$locale.'.xlf',
            $locale,
            'validators'
        );
        return $translator;

    }//end initializeTranslator()


    /**
     * Initialise le service de session.
     *
     * @return SessionService Le service de session initialisé.
     */
    public static function initializeSessionService(): SessionService
    {
        $sessionStorage = new NativeSessionStorage();
        $session        = new Session($sessionStorage);
        $session->start();
        return new SessionService($session);

    }//end initializeSessionService()


    /**
     * Initialise les services principaux et les dépendances.
     *
     * @param EnvService          $envService Le service d'environnement.
     * @param DependencyContainer $container  Le conteneur de dépendances.
     *
     * @return array Un tableau contenant les services initialisés.
     */
    public static function initializeServices(
        EnvService $envService,
        DependencyContainer $container
    ): array {

        // Initialiser le Translator.
        $translator = self::initializeTranslator('fr');

        // Initialiser le Validator en lui passant le Translator.
        $validator = Validation::createValidatorBuilder()
            ->setTranslator($translator)
            ->setTranslationDomain('validators')
            ->getValidator();

        $csrfService    = new CsrfService();
        $sessionService = self::initializeSessionService();

        return [
            'csrfService' => $csrfService,
            'securityService' => new SecurityService(),
            'emailService' => new EmailService($envService),
            'envService' => $envService,
            'sessionService' => $sessionService,
            'postsRepository' => new PostsRepository($container->getDatabase(), $validator),
            'usersRepository' => new UsersRepository($container->getDatabase(), $validator),
            'commentsRepository' => new CommentsRepository($container->getDatabase()),
            'validator' => $validator,
            'translator' => $translator,
        ];

    }//end initializeServices()


    /**
     * Initialise l'environnement Twig et ajoute les extensions nécessaires.
     *
     * @param CsrfService $csrfService Le service CSRF.
     *
     * @return Environment L'environnement Twig initialisé.
     */
    public static function initializeTwig(CsrfService $csrfService): Environment
    {
        $loader = new FilesystemLoader(__DIR__.'/../../templates');
        $twig   = new Environment($loader, ['cache' => false, 'auto_reload' => true]);
        $twig->addExtension(new CsrfExtension($csrfService));
        return $twig;

    }//end initializeTwig()


    /**
     * Récupère l'utilisateur actuellement connecté.
     *
     * @param SessionService  $sessionService  Le service de session.
     * @param UsersRepository $usersRepository Le référentiel des utilisateurs.
     *
     * @return User|null L'utilisateur courant ou null s'il n'est pas connecté.
     */
    public static function getCurrentUser(SessionService $sessionService, UsersRepository $usersRepository): ?User
    {
        if ($sessionService->has('user_id') === true) {
            $userId = $sessionService->get('user_id');
            return $usersRepository->findById($userId);
        }

        return null;

    }//end getCurrentUser()


    /**
     * Instancie le contrôleur en fonction de la route.
     *
     * @param string              $class               Nom de la classe du contrôleur.
     * @param Environment         $twig                Instance de Twig.
     * @param array               $services            Tableau des services.
     * @param UrlGeneratorService $urlGeneratorService Service de génération d'URL.
     *
     * @return object L'instance du contrôleur.
     */
    public static function getControllerInstance(
        string $class,
        Environment $twig,
        array $services,
        UrlGeneratorService $urlGeneratorService
    ) {
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

    }//end getControllerInstance()


    /**
     * Exécute une action du contrôleur avec les paramètres appropriés.
     *
     * @param object  $controllerInstance Instance du contrôleur.
     * @param string  $method             Nom de la méthode à appeler.
     * @param array   $parameters         Paramètres de la route.
     * @param Request $request            Requête HTTP.
     * @param array   $services           Tableau des services.
     *
     * @return Response La réponse de l'action du contrôleur.
     */
    public static function executeControllerAction(
        $controllerInstance,
        string $method,
        array $parameters,
        Request $request,
        array $services
    ): Response {
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
                    default => ($parameters[$paramName] ?? null),
                };
            }

            return $controllerInstance->$method(...$methodParameters);
        } catch (\ReflectionException $e) {
            return new Response('Méthode introuvable : '.$e->getMessage(), 404);
        } catch (Exception $e) {
            return new Response('Une erreur est survenue : '.$e->getMessage(), 500);
        }//end try

    }//end executeControllerAction()


    /**
     * Gère l'exécution des middlewares.
     *
     * @param Request  $request          Requête HTTP.
     * @param array    $middlewares      Liste des middlewares à exécuter.
     * @param callable $controllerAction Fonction de rappel pour l'action du contrôleur.
     *
     * @return Response La réponse après l'exécution des middlewares.
     */
    public static function handleMiddlewares(
        Request $request,
        array $middlewares,
        callable $controllerAction
    ): Response {
        $middleware = array_shift($middlewares);
        return $middleware === null ? $controllerAction($request) : $middleware->handle($request, fn($req) => self::handleMiddlewares($req, $middlewares, $controllerAction));

    }//end handleMiddlewares()


    /**
     * Gère la requête HTTP entrante et retourne la réponse.
     *
     * @param array       $services Tableau des services.
     * @param Environment $twig     Instance de Twig.
     * @param Request     $request  Requête HTTP.
     *
     * @return Response La réponse HTTP.
     */
    public static function handleRequest(
        array $services,
        Environment $twig,
        Request $request
    ): Response {
        try {
            // Initialiser le contexte, les routes et le générateur d'URL.
            $context = new RequestContext();
            $context->fromRequest($request);
            $routes  = include __DIR__.'/../config/routes.php';
            $matcher = new UrlMatcher($routes, $context);
            $urlGeneratorService = new UrlGeneratorService(new UrlGenerator($routes, $context));

            // Correspondance de la route.
            $parameters = $matcher->match($request->getPathInfo());

            // Extraction du contrôleur et de la méthode de la route.
            if (isset($parameters['_controller']) === false) {
                throw new Exception('Le contrôleur n\'est pas défini dans les paramètres de la route.');
            }

            $controller       = $parameters['_controller'];
            [$class, $method] = explode('::', $controller);

            // Vérification que la méthode n'est pas nulle.
            if (empty($method) === true) {
                throw new Exception('La méthode est indéfinie ou vide.');
            }

            // Suppression des clés réservées avant de passer les paramètres.
            unset($parameters['_controller'], $parameters['_route']);

            // Instanciation du contrôleur.
            $controllerInstance = self::getControllerInstance($class, $twig, $services, $urlGeneratorService);

            // Exécution des middlewares et de l'action du contrôleur.
            $middlewares = $request->isMethod('POST') === true ? [new CsrfMiddleware($services['csrfService'])] : [];
            $response    = self::handleMiddlewares(
                $request,
                $middlewares,
                function () use ($controllerInstance, $method, $parameters, $request, $services) {
                    return self::executeControllerAction($controllerInstance, $method, $parameters, $request, $services);
                }
            );

            return $response;
        } catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
            return new Response('Page non trouvée : '.$e->getMessage(), 404);
        } catch (Exception $e) {
            return new Response('Une erreur est survenue : '.$e->getMessage(), 500);
        }//end try

    }//end handleRequest()


}//end class
