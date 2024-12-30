# OcProjectPhpBlog

Project of a professionnal blog in php for my training as a php/symfony web developer for OpenClassrooms.

Installez les dépendances PHP avec Composer :
composer install

Structure de l'architecture
L'application suit le modèle MVC (Model-View-Controller). Voici une brève explication des dossiers principaux :

src/ : Contient tout le code source de l'application, organisé en contrôleurs, modèles, services, les vues se trouvent dans le dossier templates/.

Contrôleurs : Gère la logique de l'application et traite les requêtes.
Modèles : Gère la connexion à la base de données et la logique métier.

public/ : Contient les fichiers publics (CSS, JavaScript) accessibles depuis le navigateur ainsi que le fichier d'entrée index.php.

templates/ : Les fichiers HTML ou templates qui affichent les données à l'utilisateur.

vendor/ : Contient les dépendances installées via Composer.

Fichiers de configuration :

.env : Paramètres environnementaux.
composer.json : Gère les dépendances PHP du projet.

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/36c5ac14e4244f7ba42d5f23e3b20194)](https://app.codacy.com/gh/Adrien1988/OcProjectPhpBlog/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

Pour la présentation : 

Profil admin pour la connexion à l'application en local : 
    
    email : admin@example.com
    password : admin123