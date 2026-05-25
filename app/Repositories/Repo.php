<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\IRepo;
use PDO;
use PDOException;

abstract class Repo implements IRepo
{
    protected PDO $conn;

    protected static string $tableName;

    /**
     * Maps explicitly to your singular Entity directory layout
     * e.g., \App\Entity\Controle::class
     */
    protected static string $entityClass;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;
    }

    public function fetchById(string $id): ?object
    {
        $query = sprintf("SELECT * FROM %s WHERE id = :id LIMIT 1", static::$tableName);
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row)
                return null;

            return $this->mapRowToEntity($row);
        } catch (PDOException $e) {
            error_log("Database Error [fetchById] on table " . static::$tableName . ": " . $e->getMessage());
            return null;
        }
    }

    public function fetchAll(): array
    {
        $query = sprintf("SELECT * FROM %s", static::$tableName);
        try {
            $stmt = $this->conn->query($query);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map([$this, 'mapRowToEntity'], $rows);
        } catch (PDOException $e) {
            error_log("Database Error [fetchAll] on table " . static::$tableName . ": " . $e->getMessage());
            return [];
        }
    }

    public function delete(string $id): bool
    {
        $query = sprintf("DELETE FROM %s WHERE id = :id", static::$tableName);
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log("Database Error [delete] on table " . static::$tableName . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Unpacks database records into your singular Entity namespace instances
     */
    protected function mapRowToEntity(array $row): object
    {
        $className = static::$entityClass;
        return new $className(...array_values($row));
    }
}