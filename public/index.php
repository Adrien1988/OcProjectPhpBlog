<?php


use Dotenv\Dotenv;
use Twig\Environment;
use App\Twig\CsrfExtension;
use Models\PostsRepository;
use Models\UsersRepository;
use App\Services\EnvService;
use App\Services\CsrfService;
use Models\CommentsRepository;
use App\Services\SessionService;
use App\Core\DependencyContainer;
use App\Services\SecurityService;
use Twig\Loader\FilesystemLoader;
use App\Middlewares\CsrfMiddleware;
use App\Controllers\ErrorController;
use App\Services\EmailService;
use Symfony\Component\Validator\Validation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;


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

try {
    // Charger la configuration.
    $config = loadConfig();

    // Initialiser le conteneur de dépendances.
    $container = initializeContainer($config);

    // Instanciation du validateur.
    $validator = Validation::createValidator();

    // Création de l'instance de PostsRepository.
    $postsRepository    = new PostsRepository($container->getDatabase(), $validator);
    $usersRepository    = new UsersRepository($container->getDatabase(), $validator);
    $commentsRepository = new CommentsRepository($container->getDatabase());

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

    // Créez une instance de EmaiService.
    $emailService = new EmailService($envService);

    // Initialiser le gestionnaire de session Symfony.
    $sessionStorage = new NativeSessionStorage();
    $session        = new Session($sessionStorage);
    $session->start();

    // Créez une instance de SessionService avec la session Symfony.
    $sessionService = new SessionService($session);

    // Récupérer l'utilisateur actuellement connecté.
    $currentUser = null;
    if ($sessionService->has('user_id') === true) {
        $userId      = $sessionService->get('user_id');
        $currentUser = $usersRepository->findById($userId);
    }

    // Passer l'utilisateur actuel à Twig.
    $twig->addGlobal(
        'app',
        [
            'user' => $currentUser,
        ]
    );

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

    // Instancier le contrôleur.
    $controllerInstance = new $class($twig, $securityService, $envService, $csrfService, $sessionService, $emailService, $validator);


    // Supprimer les clés réservées de paramètres.
    unset($parameters['_controller'], $parameters['_route']);

    // Dépendances pour les middlewares.
    $dependencies = [
        'csrfService' => $csrfService,
    ];

    // Middleware à exécuter.
    if ($request->isMethod('POST') === true) {
        $middlewares = [
            new CsrfMiddleware($csrfService),
        ];
    } else {
        $middlewares = [];
    }

    // Définir une liste de méthodes autorisées par contrôleur.
    $allowedMethods = [
        'App\Controllers\PostController' => ['listPosts', 'detailPost', 'createPost', 'editPost', 'deletePost'],
        'App\Controllers\HomeController' => ['index', 'showTerms', 'showPrivacyPolicy', 'downloadCv', 'submitContact'],
        'App\Controllers\AuthController' => ['register', 'login', 'logout', 'passwordResetRequest', 'passwordReset'],
        // Ajoutez d'autres contrôleurs et méthodes si nécessaire.
    ];

    // Vérifier si la méthode est autorisée.
    if (isset($allowedMethods[$class]) === false || in_array($method, $allowedMethods[$class]) === false) {
        $response = new Response('Méthode non autorisée', 403);
        $response->send();
        exit;
    }


    // Appeler la méthode du contrôleur avec les middlewares.
    $response = handleMiddlewares(
        $request,
        $middlewares,
        function () use ($request, $controllerInstance, $method, $postsRepository, $usersRepository, $parameters, $csrfService) {
            try {
                // Utiliser la réflexion pour obtenir les paramètres de la méthode.
                $reflectionMethod = new \ReflectionMethod($controllerInstance, $method);

                // Vérifier que la méthode est publique.
                if ($reflectionMethod->isPublic() === false) {
                    throw new Exception('Méthode non accessible');
                }

                // Préparer les paramètres de la méthode.
                $methodParameters = [];

                foreach ($reflectionMethod->getParameters() as $param) {
                    $paramName = $param->getName();
                    $paramType = $param->getType();

                    if ($paramType !== null) {
                        $typeName = $paramType->getName();

                        // Vérifier si le type est une classe (objet) ou un type scalaire.
                        if ($paramType->isBuiltin() === false) {
                            // Injecter les dépendances en fonction du type.
                            if ($typeName === Request::class) {
                                $methodParameters[] = $request;
                            } else if ($typeName === PostsRepository::class) {
                                $methodParameters[] = $postsRepository;
                            } else if ($typeName === UsersRepository::class) {
                                $methodParameters[] = $usersRepository;
                            } else if ($typeName === CsrfService::class) {
                                $methodParameters[] = $csrfService;
                            } else {
                                throw new Exception("Type de paramètre non pris en charge : {$typeName}");
                            }
                        } else {
                            // Pour les types scalaires, chercher dans les paramètres de la route.
                            if (isset($parameters[$paramName]) === true) {
                                // Convertir la valeur en fonction du type attendu.
                                settype($parameters[$paramName], $typeName);
                                $methodParameters[] = $parameters[$paramName];
                            } else if ($param->isOptional() === true) {
                                // Si le paramètre est optionnel, utiliser la valeur par défaut s'il existe.
                                if ($param->isDefaultValueAvailable() === true) {
                                    $methodParameters[] = $param->getDefaultValue();
                                } else {
                                    // Si aucune valeur par défaut, utiliser null pour les types nullable.
                                    $methodParameters[] = null;
                                }
                            } else {
                                throw new Exception("Paramètre requis manquant : '{$paramName}'");
                            }
                        }//end if
                    } else if (isset($parameters[$paramName]) === true) {
                        // Utiliser les paramètres de la route.
                        $methodParameters[] = $parameters[$paramName];
                    } else if ($param->isOptional() === true) {
                        // Si le paramètre est optionnel, utiliser la valeur par défaut s'il existe.
                        if ($param->isDefaultValueAvailable() === true) {
                            $methodParameters[] = $param->getDefaultValue();
                        } else {
                            $methodParameters[] = null;
                        }
                    } else {
                        throw new Exception("Impossible de résoudre le paramètre '{$paramName}' pour la méthode '{$method}'");
                    }//end if
                }//end foreach

                // Appeler la méthode du contrôleur avec les paramètres résolus.
                return $controllerInstance->$method(...$methodParameters);
            } catch (\ReflectionException $e) {
                // Gérer les erreurs de réflexion.
                return new Response('Méthode introuvable : '.$e->getMessage(), 404);
            } catch (Exception $e) {
                // Gérer les autres exceptions.
                return new Response('Une erreur est survenue : '.$e->getMessage(), 500);
            }//end try
        },
        $dependencies
    );
} catch (Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
    $response = new Response('Page non trouvée : '.$e->getMessage(), 404);
} catch (Exception $e) {
    $response = new Response('Une erreur est survenue : '.$e->getMessage(), 500);
}//end try

// Assurez-vous que $response est défini avant de l'envoyer.
if (isset($response) === true) {
    $response->send();
} else {
    echo "An unexpected error occurred without response handling.";
}//end if
