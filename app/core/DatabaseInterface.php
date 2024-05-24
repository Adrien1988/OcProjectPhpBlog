<?php

namespace App\Core\Database;

use PDOStatement;
use Iterator;

/**
 * Interface DatabaseInterface
 *
 * Cette interface définit les méthodes standard pour interagir avec une base de données.
 * Les classes qui implémentent cette interface doivent fournir des implémentations
 * spécifiques pour les méthodes de manipulation de la base de données.
 */
interface DatabaseInterface
{


    /**
     * Exécute une requête SQL avec les paramètres donnés et retourne un itérateur de résultats.
     *
     * @param string  $sql     La requête SQL à exécuter.
     * @param array   $params  Les paramètres à lier à la requête SQL.
     * @return Iterator        Un itérateur pour les résultats de la requête.
     */
    public function query(string $sql, array $params=[]): Iterator;


    /**
     * Prépare une requête SQL avec des paramètres et retourne un objet pour exécuter cette requête.
     * Peut lever une exception si la préparation échoue.
     *
     * @param string  $sql     La chaîne de la requête SQL à préparer.
     * @param array   $params  Les paramètres à lier à la requête préparée.
     * @return PDOStatement    Un objet représentant la requête préparée.
     */
    public function prepare(string $sql): \PDOStatement;


    /**
     * Exécute une déclaration PDO avec les paramètres donnés.
     *
     * @param \PDOStatement  $stmt   La déclaration PDO à exécuter.
     * @param array          $params Les paramètres à lier à la déclaration.
     * @return bool         Retourne true si l'exécution a réussi, sinon false.
     */
    public function execute(\PDOStatement $stmt, array $params=[]): bool;


    /**
     * Retourne l'identifiant de la dernière ligne insérée ou la valeur d'une séquence.
     * Peut lever une exception si la récupération de l'ID échoue.
     *
     * @return string L'identifiant de la dernière ligne insérée sous forme de chaîne.
     */
    public function lastInsertId(): string;


}
