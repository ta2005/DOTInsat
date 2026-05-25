<?php
declare(strict_types=1);

/**
 * Abstract base repository.
 * Holds the PDO connection and provides generic fetchAll / fetchById
 * built on the static $tableName and $entityClass properties
 * that each concrete repository must define.
 */
abstract class Repo implements IRepo {
    protected ?PDO $conn;

    /** Subclasses set this to their actual table name, e.g. 'controle' */
    protected static string $tableName  = '';

    /** Subclasses set this to their entity FQCN, e.g. Controle::class */
    protected static string $entityClass = '';

    public function __construct(?PDO $conn) {
        $this->conn = $conn;
    }

    /**
     * Returns all rows from the table, mapped through the concrete
     * class's mapToEntity() method if defined, or as plain arrays.
     */
    public function fetchAll(): array {
        if ($this->conn === null) {
            return [];
        }
        try {
            $stmt = $this->conn->query('SELECT * FROM ' . static::$tableName);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (method_exists($this, 'mapToEntity')) {
                return array_map(fn($row) => $this->mapToEntity($row), $rows);
            }
            return $rows;
        } catch (PDOException $e) {
            error_log('Repo::fetchAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Returns a single row by primary key 'id'.
     */
    public function fetchById(int $id): mixed {
        if ($this->conn === null) {
            return null;
        }
        try {
            $stmt = $this->conn->prepare(
                'SELECT * FROM ' . static::$tableName . ' WHERE id = :id LIMIT 1'
            );
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) return null;

            if (method_exists($this, 'mapToEntity')) {
                return $this->mapToEntity($row);
            }
            return $row;
        } catch (PDOException $e) {
            error_log('Repo::fetchById error: ' . $e->getMessage());
            return null;
        }
    }
}
