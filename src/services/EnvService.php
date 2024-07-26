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
     * Initialise Dotenv avec le chemin spécifié et charge les variables d'environnement.
     *
     * @param string $path Le chemin vers le fichier .env.
     */
    public function __construct(string $path)
    {
        $this->dotenv       = Dotenv::createImmutable($path);
        $this->envVariables = $this->dotenv->load();

    }//end __construct()


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
        return ($this->envVariables[$key] ?? $default);

    }//end getEnv()


}//end class
