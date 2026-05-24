<?php
declare(strict_types=1);

class GroupRepo {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function fetchAll(): array {
        try {
            $stmt = $this->conn->query("SELECT * FROM groups");
            return array_map(fn($row) => new Group(
                $row['nom'],
                new DateTimeImmutable($row['date_creation']),
                $row['id_mod'] ? (int)$row['id_mod'] : null
            ), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function create(string $nom, int $id_mod): ?int {
        $query = "INSERT INTO groups (nom, id_mod) VALUES (:nom, :mod) RETURNING id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nom' => $nom, 'mod' => $id_mod]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}
