<?php

namespace Models;

use App\Models\User;
use App\Core\Database\DatabaseInterface;
use DateTime;

class UsersRepository
{
    private DatabaseInterface $db;

    /**
     * Constructeur qui injecte la dépendance vers la couche d'accès aux données.
     *
     * @param DatabaseInterface $db Interface pour interagir avec la base de données.
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    
    
}
