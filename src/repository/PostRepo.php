<?php
declare(strict_types=1);

class PostRepo {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function fetchAll(): array {
        try {
            $stmt = $this->conn->query("SELECT * FROM post ORDER BY date_de_creation DESC");
            return array_map(fn($row) => $this->mapToEntity($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function fetchByGroup(int $groupId): array {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM post WHERE id_group = ? ORDER BY date_de_creation DESC");
            $stmt->execute([$groupId]);
            return array_map(fn($row) => $this->mapToEntity($row), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function create(string $contenu, int $id_user, ?int $id_group = null): ?int {
        $query = "INSERT INTO post (contenu, id_user, id_group) 
                  VALUES (:content, :user, :grp) RETURNING id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'content' => $contenu,
                'user' => $id_user,
                'grp' => $id_group
            ]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Helper to convert database row to Post object
     */
    private function mapToEntity(array $row): Post {
        return new Post(
            $row['contenu'],
            (int)$row['id_user'],
            $row['id_group'] ? (int)$row['id_group'] : null,
            new DateTimeImmutable($row['date_de_creation']),
            (int)$row['id']
        );
    }
}
