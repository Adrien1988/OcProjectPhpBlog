<?php

namespace App\Init;

use App\Core\DependencyContainer;

/**
 * Initialise le conteneur de dépendances de l'application.
 */
class ContainerInit
{


    /**
     * Initialise le conteneur de dépendances avec la configuration donnée.
     *
     * @param array $config La configuration de l'application.
     *
     * @return DependencyContainer Le conteneur de dépendances initialisé.
     */
    public function initialize(array $config): DependencyContainer
    {
        return new DependencyContainer(
            [
                'dsn' => 'mysql:host='.$config['database']['host'].';dbname='.$config['database']['dbname'].';charset=utf8mb4',
                'db_user' => $config['database']['user'],
                'db_password' => $config['database']['password'],
            ]
        );

    }//end initialize()


}//end class
