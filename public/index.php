<?php

use App\Core\Application;

/*
 * Point d'entrée de l'application.
 *
 * Ce fichier initialise l'autoloader de Composer, crée une instance de l'application
 * et exécute la méthode run() pour lancer le traitement de la requête.
 */

// Inclusion de l'autoloader de Composer.
require __DIR__.'/../vendor/autoload.php';


$app = new Application();

$app->run();
