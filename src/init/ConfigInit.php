<?php

namespace App\Init;

use App\Services\EnvService;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Charge la configuration de l'application.
 */
class ConfigInit
{

    /**
     * Instance du service pour gérer les variables d'environnement.
     *
     * @var EnvService
     */
    private EnvService $envService;


    /**
     * Constructeur pour injecter le service d'environnement.
     *
     * @param EnvService $envService Le service pour gérer les variables d'environnement.
     */
    public function __construct(EnvService $envService)
    {
        $this->envService = $envService;

    }//end __construct()


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
        $config     = [];

        // Charger le fichier config.php si disponible.
        if (file_exists($configPath) === true) {
            include $configPath;
            if (function_exists('getDatabaseConfig') === true) {
                $config = getDatabaseConfig($this->envService);
            }
        }

        // Vérifier la présence des clés requises (sauf pour un mot de passe vide explicitement autorisé).
        $requiredKeys = ['host', 'dbname', 'user', 'password'];
        foreach ($requiredKeys as $key) {
            if ($key !== 'password' && (empty($config['database'][$key] ?? null) === true)) {
                throw new Exception("La variable de configuration '{$key}' est manquante ou vide dans la section 'database'.");
            }

            if ($key === 'password' && isset($config['database'][$key]) === false) {
                throw new Exception("La variable de configuration '{$key}' est manquante dans la section 'database'.");
            }
        }

        return $config;

    }//end load()


}//end class
