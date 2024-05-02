<?php

namespace App\Core;

use App\Core\Database\MySQLDatabase;
use App\Core\Database\DatabaseInterface;
use PDO;

class DependencyContainer
{
    private $configurations;
    private $instances = [];

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    public function getDatabase(): DatabaseInterface {
        if (!isset($this->instances['database'])) {
            $pdo = new PDO(
                $this->configurations['dsn'],
                $this->configurations['db_user'],
                $this->configurations['db_password']
            );
            $this->instances['database'] = new MySQLDatabase($pdo);
        }
        return $this->instances['databse'];
    }
}
