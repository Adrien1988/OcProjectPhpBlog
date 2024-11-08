<?php

use App\Core\Application;
use Symfony\Component\HttpFoundation\Request;

/*
 * Point d'entrée de l'application.
 *
 * Ce fichier initialise l'autoloader de Composer, crée une instance de l'application
 * et exécute la méthode run() pour lancer le traitement de la requête.
 */

// Inclusion de l'autoloader de Composer.
require __DIR__.'/../vendor/autoload.php';

$request = Request::createFromGlobals();


$app = new Application($request);

$app->run();
