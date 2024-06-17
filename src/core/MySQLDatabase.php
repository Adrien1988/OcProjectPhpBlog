<?php

namespace App\Core\Database;

use PDO;
use PDOStatement;
use Iterator;

/**
 * Classe MySQLDatabase qui implémente DatabaseInterface pour gérer les interactions avec la base de données MySQL.
 */
class MySQLDatabase implements DatabaseInterface
{

    /**
     * Instance PDO pour les interactions avec la base de données.
     *
     * @var PDO
     */
    private PDO $pdo;


    /**
     * Constructeur qui initialise l'objet MySQLDatabase avec une instance de PDO.
     *
     * @param PDO $pdo L'instance PDO à utiliser pour les interactions avec la base de données.
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;

    }//end __construct()


    /**
     * Exécute une requête SQL avec des paramètres et retourne les résultats.
     *
     * @param string $sql    La requête SQL à exécuter.
     * @param array  $params Les paramètres à associer à la requête SQL, sous forme de tableau associatif.
     *
     * @return Iterator      Les résultats de la requête sous forme d'Iterator.
     */
    public function query(string $sql, array $params=[]): Iterator
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC) === true) {
            yield $row;
        }

    }//end query()


    /**
     * Prépare une requête SQL à exécuter avec des paramètres.
     *
     * @param string $sql La requête SQL à préparer.
     *
     * @return PDOStatement L'objet PDOStatement préparé.
     */
    public function prepare(string $sql): PDOStatement
    {
        return $this->pdo->prepare($sql);

    }//end prepare()


    /**
     * Exécute un PDOStatement préparé avec des paramètres.
     *
     * @param PDOStatement $stmt   Le PDOStatement à
     *                             exécuter.
     * @param array        $params Les paramètres à associer.
     *
     * @return bool        Le succès de l'exécution.
     */
    public function execute(PDOStatement $stmt, array $params=[]): bool
    {
        return $stmt->execute($params);

    }//end execute()


    /**
     * Retourne l'ID de la dernière ligne insérée.
     *
     * @return string L'ID de la dernière ligne insérée, retourné sous forme de chaîne.
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();

    }//end lastInsertId()


}//end class
