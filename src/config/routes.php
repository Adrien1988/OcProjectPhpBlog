<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();
$routes->add('home', new Route('/home', ['_controller' => 'App\Controllers\HomeController::index']));

return $routes;
