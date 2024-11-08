<?php

namespace App\Init;

use App\Services\EnvService;
use Dotenv\Loader\Loader;
use Dotenv\Parser\Parser;
use Dotenv\Store\StoreBuilder;
use Dotenv\Repository\RepositoryBuilder;

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
        $repository = RepositoryBuilder::createWithDefaultAdapters()->make();

        $store = StoreBuilder::createWithNoNames()
            ->addPath(__DIR__.'/../../')
            ->addName('.env')
            ->make();

        $content = $store->read();

        $parser  = new Parser();
        $entries = $parser->parse($content);

        $loader = new Loader();

        foreach ($entries as $entry) {
            $loader->load($repository, [$entry]);
        }

        return new EnvService($repository);

    }//end initialize()


}//end class
