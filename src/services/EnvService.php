<?php

namespace App\Services;

use Dotenv\Dotenv;

/**
 * Service pour charger les variables d'environnement.
 */
class EnvService
{

    /**
     * Instance de Dotenv pour gérer les variables d'environnement.
     *
     * @var Dotenv
     */
    private Dotenv $dotenv;

    /**
     * Les variables d'environnement chargées.
     *
     * @var array
     */
    private array $envVariables = [];


    /**
     * Constructeur de la classe.
     *
     * @param Dotenv $dotenv Instance de Dotenv.
     */
    public function __construct(Dotenv $dotenv)
    {
        $this->dotenv = $dotenv;
        $this->loadEnv();

    }//end __construct()


    /**
     * Charge les variables d'environnement à partir du fichier .env.
     *
     * @return void
     */
    public function loadEnv(): void
    {
        $this->dotenv->load();

        // Stocker les variables d'environnement dans une propriété privée.
        $this->envVariables = getenv();

    }//end loadEnv()


    /**
     * Récupère la valeur d'une variable d'environnement.
     *
     * @param string $key     La clé de la variable d'environnement.
     * @param mixed  $default La valeur par défaut si la clé n'existe pas.
     *
     * @return mixed La valeur de la variable d'environnement ou la valeur par défaut.
     */
    public function getEnv(string $key, $default=null)
    {
        return $this->envVariables[$key] ?? $default;

    }//end getEnv()


}//end class
