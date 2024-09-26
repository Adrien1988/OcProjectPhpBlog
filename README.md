# OcProjectPhpBlog

Project of a professionnal blog in php for my training as a php/symfony web developer for OpenClassrooms.

Installez les dépendances PHP avec Composer :
composer install

Installez les dépendances Node.js :
yarn install

Structure de l'architecture
L'application suit le modèle MVC (Model-View-Controller). Voici une brève explication des dossiers principaux :

src/ : Contient tout le code source de l'application, organisé en contrôleurs, modèles, les vues se trouvent dans le dossier templates/.

Contrôleurs : Gère la logique de l'application et traite les requêtes.
Modèles : Gère la connexion à la base de données et la logique métier.

public/ : Contient les fichiers publics (HTML, CSS, JavaScript) accessibles depuis le navigateur.

templates/ : Les fichiers HTML ou templates qui affichent les données à l'utilisateur.

vendor/ : Contient les dépendances installées via Composer.

Fichiers de configuration :

.env : Paramètres environnementaux.
composer.json : Gère les dépendances PHP du projet.
