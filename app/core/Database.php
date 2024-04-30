<?php

namespace App\Core;

require_once __DIR__ . '/../config/config.php';

// On "importe" PDO
use PDO;
use PDOException;

class Database extends PDO {

    // Instance unique de la classe
    private static $instance = null;

    private function __construct() 
    {
        // DSN de connexion
        $dsn = $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    }
}
