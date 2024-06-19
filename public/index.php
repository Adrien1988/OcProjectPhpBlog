<?php

use App\Models\Post;
use App\Models\User;
use Twig\Environment;
use App\Models\Comment;
use App\Core\DependencyContainer;
use Twig\Loader\FilesystemLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Config\Definition\Exception\Exception;

// Afficher toutes les erreurs de PHP (recommandé pour le développement).
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Début du fichier index.php<br>";


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
    echo "Chemin du fichier de configuration : $configPath<br>";

    if (file_exists($configPath) === false) {
        throw new Exception('Le fichier de configuration n\'existe pas.');
    }

    // Inclure le fichier de configuration et appeler la fonction getDatabaseConfig.
    include $configPath;
    $config = getDatabaseConfig();

    echo "Contenu de la configuration : ";
    var_dump($config);

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


// Inclusion des fichiers nécessaires après les déclarations de fonctions.
require __DIR__.'/../vendor/autoload.php';

// Logique d'exécution après les déclarations et inclusions.
try {
    echo "Avant le chargement de la configuration<br>";

    // Charger la configuration.
    $config = loadConfig();

    echo "Configuration chargée<br>";

    // Initialiser le conteneur de dépendances.
    $container = initializeContainer($config);

    echo "Conteneur de dépendances initialisé<br>";

    // Création des modèles et injection de l'instance de base de données à partir du conteneur.
    $postModel    = new Post($container->getDatabase());
    $userModel    = new User($container->getDatabase());
    $commentModel = new Comment($container->getDatabase());

    echo "Modèles créés<br>";

    // Configurer Twig.
    $loader = new FilesystemLoader(__DIR__.'/../templates');
    $twig   = new Environment(
        $loader,
        [
            'cache' => __DIR__.'/../cache',
        ]
    );

    echo "Twig configuré<br>";

    // Charger les routes.
    $routes = include __DIR__.'/../src/config/routes.php';

    echo "Routes chargées<br>";

    // Initialiser le contexte de la requête.
    $context = new RequestContext();
    $request = Request::createFromGlobals();
    $context->fromRequest($request);

    echo "Contexte de la requête initialisé<br>";

    // Initialiser le matcher et le générateur d'URL.
    $matcher   = new UrlMatcher($routes, $context);
    $generator = new UrlGenerator($routes, $context);

    echo "Matcher et générateur d'URL initialisés<br>";

    // Matcher la requête à une route.
    $parameters = $matcher->match($request->getPathInfo());

    echo "Requête matchée<br>";

    // Extraire le contrôleur et l'action.
    $controller           = $parameters['_controller'];
    list($class, $method) = explode('::', $controller);

    echo "Contrôleur extrait : $class, méthode : $method<br>";

    // Instancier le contrôleur et appeler l'action.
    $controllerInstance = new $class();

    // Supprimer les clés réservées de paramètres comme '_controller'.
    unset($parameters['_controller']);

    // Appeler la méthode du contrôleur avec les paramètres extraits.
    $response = $controllerInstance->$method(...array_values($parameters));

    // Envoyer la réponse.
    $response->send();
} catch (Exception $e) {
    // Gestion des erreurs (par exemple, route non trouvée).
    echo "Erreur attrapée : ".$e->getMessage()."<br>";
    $response = new Response('Not Found', 404);
    $response->send();
}//end try

echo "Fin du fichier index.php<br>";
