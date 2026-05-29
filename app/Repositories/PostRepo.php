<?php
declare(strict_types=1);

class PostRepo {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    // Tous les posts avec infos auteur, triés par date
    public function fetchAll(): array {
        try {
            $stmt = $this->conn->query("
                SELECT p.*, u.nom, u.prenom
                FROM post p
                JOIN users u ON u.id = p.auteur_id
                ORDER BY p.date_de_creation DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Posts d'un groupe spécifique avec infos auteur
    public function fetchByGroup(int $groupId): array {
        try {
            $stmt = $this->conn->prepare("
                SELECT p.*, u.nom, u.prenom
                FROM post p
                JOIN users u ON u.id = p.auteur_id
                WHERE p.groupe_id = ?
                ORDER BY p.date_de_creation DESC
            ");
            $stmt->execute([$groupId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Posts des groupes rejoints par un utilisateur
    public function fetchFeedByUserId(int $userId): array {
        try {
            $stmt = $this->conn->prepare("
                SELECT p.*, u.nom, u.prenom, g.nom AS groupe_nom
                FROM post p
                JOIN users u ON u.id = p.auteur_id
                LEFT JOIN groupe g ON g.id = p.groupe_id
                WHERE p.groupe_id IN (
                    SELECT groupe_id FROM membre_groupe WHERE user_id = :uid
                )
                ORDER BY p.date_de_creation DESC
            ");
            $stmt->execute(['uid' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function create(string $contenu, int $id_user, ?int $id_group = null): ?int {
        $query = "INSERT INTO post (contenu, auteur_id, groupe_id)
                  VALUES (:content, :user, :grp) RETURNING id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'content' => $contenu,
                'user'    => $id_user,
                'grp'     => $id_group
            ]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function delete(int $id, int $auteurId): bool {
        try {
            $stmt = $this->conn->prepare(
                "DELETE FROM post WHERE id = :id AND auteur_id = :uid"
            );
            return $stmt->execute(['id' => $id, 'uid' => $auteurId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
