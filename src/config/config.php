<?php


/**
 * Retourne un tableau de configuration pour la base de données.
 *
 * @return array La configuration de la base de données.
 */
function getDatabaseConfig(): array
{
    return [
        'database' => [
            'host'     => 'localhost',
            'dbname'   => 'php_blog',
            'user'     => 'root',
            'password' => '',
        ],
    ];

}//end getDatabaseConfig()


// Appelle la fonction pour retourner la configuration de la base de données.
return getDatabaseConfig();
