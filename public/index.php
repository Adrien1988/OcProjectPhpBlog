<?php

require_once __DIR__.'/../vendor/autoload.php';

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

$config = require(__DIR__.'/../config/config.php');

if ($config === false || !isset($config['database'])) {
    throw new Exception('Configuration de la base de données introuvable.');
}

// Initialiser le conteneur d'injection de dépendance avec les paramètres de la base de données.
$container = new DependencyContainer([
                                      'dsn' => 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['dbname'] . ';charset=utf8mb4',
                                      'db_user' => $config['database']['user'],
                                      'db_password' => $config['database']['password']
                                    ]);

// Création des modèles et injection de l'instance de base de données à partir du conteneur.
$postModel = new Post($container->getDatabase());
$userModel = new User($container->getDatabase());
$commentModel = new Comment($container->getDatabase());

// Configurer Twig.
$loader = new FilesystemLoader(__DIR__.'/../templates');
$twig = new Environment(
    $loader, [
              'cache' => __DIR__.'/../cache',
             ]
);

// Charger les routes.
$routes = include_once __DIR__.'/../config/routes.php';

// Initialiser le contexte de la requête.
$context = new RequestContext();
$request = Request::createFromGlobals();
$context->fromRequest($request);

// Initialiser le matcher et le générateur d'URL.
$matcher = new UrlMatcher($routes, $context);
$generator = new UrlGenerator($routes, $context);

try {
    // Matcher la requête à une route.
    $parameters = $matcher->match($request->getPathInfo());

    // Extraire le contrôleur et l'action.
    $controller = $parameters['_controller'];
    list($class, $method) = explode('::', $controller);

    // Instancier le contrôleur et appeler l'action.
    $controllerInstance = new $class();

    // Supprimer les clés réservées de paramètres comme '_controller'.
    unset($parameters['_controller']);

    $response = $controllerInstance->$method(...array_values($parameters));

    // Envoyer la réponse.
    $response->send();
} catch (Exception $e) {
    // Gestion des erreurs (par exemple, route non trouvée).
    $response = new Response('Not Found', 404);
    $response->send();
}
