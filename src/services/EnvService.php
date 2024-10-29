<?php

namespace App\Services;

use Dotenv\Repository\RepositoryInterface;

/**
 * Service pour charger les variables d'environnement.
 */
class EnvService
{
    /**
     * Les variables d'environnement chargées.
     *
     * @var RepositoryInterface
     */
    private RepositoryInterface $repository;

    /**
     * Constructeur de la classe.
     *
     * @param RepositoryInterface $repository Instance du dépôt des variables d'environnement.
     */
    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Récupère la valeur d'une variable d'environnement.
     *
     * @param string $key     La clé de la variable d'environnement.
     * @param mixed  $default La valeur par défaut si la clé n'existe pas.
     *
     * @return mixed La valeur de la variable d'environnement ou la valeur par défaut.
     */
    public function getEnv(string $key, $default = null)
    {
        $value = $this->repository->get($key);
        return $value !== null ? $value : $default;
    }
}
