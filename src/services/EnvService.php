<?php

namespace App\Services;

use Dotenv\Dotenv;

/**
 * Service pour charger les variables d'environnement.
 */
class EnvService
{

    /**
     * Les variables d'environnement chargées.
     *
     * @var array
     */
    private array $envVariables = [];


    /**
     * Constructeur de la classe.
     *
     * @param Dotenv $dotenv Instance de Dotenv pour charger les variables d'environnement.
     */
    public function __construct(Dotenv $dotenv)
    {
        $this->envVariables = $dotenv->load();

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
