<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;


/**
 * Crée une nouvelle collection de routes et ajoute une route pour la page d'accueil.
 *
 * @return RouteCollection La collection de routes avec la route ajoutée.
 */
function createRoutes(): RouteCollection
{
    $routes = new RouteCollection();
    $routes->add('home', new Route('/', ['_controller' => 'App\Controllers\HomeController::index']));

    return $routes;

}//end createRoutes()


// Appelle la fonction pour créer et retourner la collection de routes.
return createRoutes();
