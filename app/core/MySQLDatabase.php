<?php

namespace App\Core\Database;



// On "importe" PDO
use PDO;


/**
 * Classe MySQLDatabase qui implémente DatabaseInterface pour gérer les interactions avec la base de données MySQL.
 * Cette classe encapsule les fonctions de base de données, permettant une réutilisation facile et une maintenance centralisée.
 */
class MySQLDatabase implements DatabaseInterface
{
    private PDO $pdo;

    /**
     * Constructeur qui initialise l'objet MySQLDatabase avec une instance de PDO.
     * @param PDO $pdo L'instance PDO à utiliser pour les interactions avec la base de données.
     */
    private function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Exécute une requête SQL avec des paramètres et retourne les résultats.
     * @param string $sql La requête SQL à exécuter.
     * @param array $params Les paramètres à associer à la requête SQL, sous forme de tableau associatif.
     * @return array Les résultats de la requête sous forme de tableau associatif.
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Prépare et exécute une requête SQL avec des paramètres, similaire à la méthode query.
     * Cette méthode est actuellement un duplicata de query() et pourrait être utilisée pour implémenter des comportements différents si nécessaire.
     * @param string $sql La requête SQL à préparer.
     * @param array $params Les paramètres de la requête.
     * @return array Les données récupérées de la base de données.
     */
    public function prepare(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retourne l'ID de la dernière ligne insérée.
     *
     * Cette méthode appelle la fonction PDO native lastInsertId pour obtenir l'ID généré
     * pour la dernière insertion effectuée via l'objet PDO actuel. Elle est typiquement utilisée
     * après une insertion dans une table avec une colonne ID auto-incrémentée.
     * 
     * @return string L'ID de la dernière ligne insérée, retourné sous forme de chaîne.
     */
    public function lastInsertId(): string {
        return $this->pdo->lastInsertId();
    }
}
