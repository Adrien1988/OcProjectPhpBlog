<?php

namespace App\Core\Database;

interface DatabaseInterface
{
    public function query(string $sql, array $params = []): array;
    public function prepare(string $sql, array $params = []): array;
    public function lastInsertId(): string;
}
