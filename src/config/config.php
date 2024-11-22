<?php

use App\Services\EnvService;


/**
 * Retourne un tableau de configuration pour la base de données.
 *
 * @param EnvService $envService Le service d'environnement permettant de récupérer les variables.
 *
 * @return array La configuration de la base de données.
 */
function getDatabaseConfig(EnvService $envService): array
{
    return [
        'database' => [
            'host'     => $envService->getenv('DB_HOST'),
            'dbname'   => $envService->getenv('DB_NAME'),
            'user'     => $envService->getenv('DB_USER'),
            'password' => $envService->getenv('DB_PASSWORD'),
        ],
    ];

}//end getDatabaseConfig()
