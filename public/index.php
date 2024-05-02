<?php

// Charger les bibliothèques/classes nécessaires via Composer
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\DependencyContainer;
use App\Models\PostModel;
use App\Models\UserModel;
use App\Models\CommentModel;

// Charger la configuration de la base de données à partir du fichier config.php
$config = require __DIR__ . '/../config/config.php';

// Initialiser le conteneur d'injection de dépendance avec les paramètres de la base de données
$container = new DependencyContainer([
    // Construction de la chaîne DSN pour PDO à partir de la configuration
    'dsn' => 'mysql:host=' . $config['database']['host'] . ';dbname=' . $config['database']['dbname'] . ';charset=utf8mb4',
    'db_user' => $config['database']['user'], // Utilisateur de la base de données
    'db_password' => $config['database']['password']  // Mot de passe de la base de données
]);

// Création des modèles et injection de l'instance de base de données à partir du conteneur
$postModel = new PostModel($container->getDatabase()); // Modèle pour gérer les articles
$userModel = new UserModel($container->getDatabase());  // Modèle pour gérer les utilisateurs
$commentModel = new CommentModel($container->getDatabase()); // Modèle pour gérer les commentaires

// Les objets modèles sont maintenant prêts à être utilisés pour la logique d'affaires.