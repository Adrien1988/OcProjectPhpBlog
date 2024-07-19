<?php

namespace App\Services;

use Dotenv\Dotenv;

/**
 * Service pour charger les variables d'environnement.
 */
class EnvService
{


    /**
     * Charge les variables d'environnement à partir du fichier .env.
     *
     * @param string $directory Le répertoire contenant le fichier .env.
     *
     * @return void
     */
    public function loadEnv(string $directory):  void
    {
        $dotenv = Dotenv::createImmutable($directory);
        $dotenv->load();

    }//end loadEnv()


}//end class
