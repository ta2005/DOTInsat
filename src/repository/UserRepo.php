<?php
declare(strict_types=1);

class UserRepo {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function fetchAll(): array {
        try {
            $stmt = $this->conn->query("SELECT * FROM users ORDER BY nom ASC");
            return array_map(fn($row) => new User(
                (int)$row['cin'],
                $row['nom'],
                $row['prenom'],
                $row['email'],
                (int)$row['id']
            ), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function fetchById(int $id): ?User {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;

            return new User((int)$row['cin'], $row['nom'], $row['prenom'], $row['email'], (int)$row['id']);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function create(int $cin, string $nom, string $prenom, string $email, string $password): ?int {
        $query = "INSERT INTO users (cin, nom, prenom, email, password) 
                  VALUES (:cin, :nom, :prenom, :email, :pass) RETURNING id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'cin' => $cin, 'nom' => $nom, 'prenom' => $prenom, 
                'email' => $email, 'pass' => $password
            ]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function delete(int $id): void {
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }
}
