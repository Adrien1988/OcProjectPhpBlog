<?php

use App\Init\Initializer;
use Symfony\Component\HttpFoundation\Request;

// Inclusion de l'autoloader.
require __DIR__.'/../vendor/autoload.php';

// Initialiser les composants de base.
$envService = Initializer::initializeEnvironment();
$config     = Initializer::loadConfig();
$container  = Initializer::initializeContainer($config);


// Initialiser les services et dépendances.
$services = Initializer::initializeServices($envService, $container, $validator);

// Configuration Twig et récupération de l'utilisateur.
$twig = Initializer::initializeTwig($services['csrfService']);

$currentUser = Initializer::getCurrentUser($services['sessionService'], $services['usersRepository']);
$twig->addGlobal('app', ['user' => $currentUser]);

// Initialiser le contexte, les routes et le générateur d'URL.
$request = Request::createFromGlobals();

// Traitement de la requête et obtention de la réponse.
$response = Initializer::handleRequest($services, $twig, $request);

// Envoi de la réponse.
$response->send();
