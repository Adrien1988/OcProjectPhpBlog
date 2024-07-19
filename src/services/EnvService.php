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
     * Constructeur de la classe.
     *
     * @param Dotenv $dotenv Instance de Dotenv.
     */
    public function __construct(Dotenv $dotenv)
    {
        $this->dotenv = $dotenv;
    }

    /**
     * Charge les variables d'environnement à partir du fichier .env.
     *
     * @return void
     */
    public function loadEnv(): void
    {
        $this->dotenv->load();
    }
}
