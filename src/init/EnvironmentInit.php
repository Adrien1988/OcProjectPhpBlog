<?php

namespace App\Init;

use RuntimeException;
use Dotenv\Loader\Loader;
use Dotenv\Parser\Parser;
use App\Services\EnvService;
use Dotenv\Store\StoreBuilder;
use Dotenv\Repository\RepositoryBuilder;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Initialise les variables d'environnement en utilisant la bibliothèque Dotenv.
 */
class EnvironmentInit
{


    /**
     * Charge les variables d'environnement à partir du fichier .env.
     *
     * @return EnvService Le service d'environnement contenant les variables chargées.
     */
    public function initialize(): EnvService
    {
        try {
            $repository = RepositoryBuilder::createWithDefaultAdapters()->make();

            $store = StoreBuilder::createWithNoNames()
                ->addPath(__DIR__.'/../../')
                ->addName('.env')
                ->make();

            if (file_exists(__DIR__.'/../../.env') === false) {
                throw new RuntimeException('Le fichier .env est introuvable.');
            }

            $content = $store->read();

            $parser  = new Parser();
            $entries = $parser->parse($content);

            $loader = new Loader();

            foreach ($entries as $entry) {
                $loader->load($repository, [$entry]);
            }

            // Validation des clés requises.
            $requiredKeys = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'];
            foreach ($requiredKeys as $key) {
                if ($repository->get($key) === null) {
                    throw new RuntimeException("La variable d'environnement obligatoire {$key} est manquante dans le fichier .env.");
                }
            }

            return new EnvService($repository);
        } catch (Exception $e) {
            throw new RuntimeException('Erreur lors de l\'initialisation des variables d\'environnement : '.$e->getMessage());
        }//end try

    }//end initialize()


}//end class
