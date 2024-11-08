<?php

namespace App\Init;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Charge la configuration de l'application.
 */
class ConfigInit
{


    /**
     * Charge la configuration de la base de données à partir du fichier config.php.
     *
     * @return array La configuration sous forme de tableau.
     *
     * @throws Exception Si le fichier de configuration n'existe pas ou est invalide.
     */
    public function load(): array
    {
        $configPath = __DIR__.'/../config/config.php';
        if (file_exists($configPath) === false) {
            throw new Exception('Le fichier de configuration n\'existe pas.');
        }

        include $configPath;
        $config = getDatabaseConfig();

        if ($config === false || isset($config['database']) === false) {
            throw new Exception('Configuration de la base de données introuvable.');
        }

        return $config;

    }//end load()


}//end class
