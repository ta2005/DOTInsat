<?php
declare(strict_types=1);

class UserRepo {
    private ?PDO $conn;

    public function __construct(?PDO $conn) {
        $this->conn = $conn;
    }

    public function fetchAll(): array {
        if ($this->conn === null) {
            return [
                new User(11111111, "Aymen", "Admin", "aymen@blog.com", 1),
                new User(22222222, "Sarah", "Writer", "sarah@blog.com", 2),
                new User(33333333, "John", "Explorer", "john@blog.com", 3)
            ];
        }
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
        if ($this->conn === null) {
            if ($id === 1) return new User(11111111, "Aymen", "Admin", "aymen@blog.com", 1);
            if ($id === 2) return new User(22222222, "Sarah", "Writer", "sarah@blog.com", 2);
            if ($id === 3) return new User(33333333, "John", "Explorer", "john@blog.com", 3);
            return new User(99999999, "Mock", "Student", "student@example.com", $id);
        }
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
        if ($this->conn === null) {
            return rand(1000, 9999);
        }
        $query = "INSERT INTO users (cin, nom, prenom, email, mot_passe) 
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
        if ($this->conn === null) {
            return;
        }
        try {
            $stmt = $this->conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
        }
    }
}
