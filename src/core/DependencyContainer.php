<?php

namespace App\Core;

use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseInterface;
use PDO;

/**
 * Classe DependencyContainer
 *
 * Cette classe gère les dépendances de l'application, notamment la base de données.
 */
class DependencyContainer
{

    /**
     * Configuration pour les dépendances.
     *
     * @var array
     */
    private $configurations;

    /**
     * Instances des objets gérés par le conteneur.
     *
     * @var array
     */
    private $instances = [];


    /**
     * Constructeur de la classe DependencyContainer.
     *
     * @param array $configurations Configuration pour les dépendances.
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;

    }


    /**
     * Récupère l'instance de la base de données.
     *
     * @return DatabaseInterface Retourne une instance de la base de données.
     */
    public function getDatabase(): DatabaseInterface
    {
        if (!isset($this->instances['database'])) {
            $pdo = new PDO(
                $this->configurations['dsn'],
                $this->configurations['db_user'],
                $this->configurations['db_password']
            );
            $this->instances['database'] = new MySQLDatabase($pdo);
        }

        return $this->instances['database'];

    }
}
