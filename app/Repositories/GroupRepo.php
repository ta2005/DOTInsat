<?php
declare(strict_types=1);

class GroupRepo {
    private PDO $conn;

    public function __construct(PDO $conn) {
        $this->conn = $conn;
    }

    public function fetchAll(): array {
        try {
            $stmt = $this->conn->query("SELECT * FROM groupe");
            return array_map(fn($row) => new Group(
                $row['nom'],
                new DateTimeImmutable($row['date_creation']),
                $row['moderateur_id'] ? (int)$row['moderateur_id'] : null
            ), $stmt->fetchAll(PDO::FETCH_ASSOC));
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function create(string $nom, int $id_mod): ?int {
        $query = "INSERT INTO groupe (nom, moderateur_id) VALUES (:nom, :mod) RETURNING id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['nom' => $nom, 'mod' => $id_mod]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    // ll groupe illi aad ll user hadheka
    public function fetchGroupsByUserId(int $userId): array {
        $query = "SELECT g.* FROM groupe g
                  JOIN membre_groupe mg ON g.id = mg.groupe_id
                  WHERE mg.user_id = :user_id
                  ORDER BY g.nom ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // Groupe mahouch fehom
    public function fetchUnjoinedGroups(int $userId): array {
        $query = "SELECT * FROM groupe
                  WHERE id NOT IN (
                      SELECT groupe_id FROM membre_groupe WHERE user_id = :user_id
                  )
                  ORDER BY nom ASC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // todkhe ll groupe
    public function joinGroup(int $userId, int $groupId): bool {
        $query = "INSERT INTO membre_groupe (user_id, groupe_id) VALUES (:user_id, :groupe_id)";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['user_id' => $userId, 'groupe_id' => $groupId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // tokhrej mll groupe
    public function leaveGroup(int $userId, int $groupId): bool {
        $query = "DELETE FROM membre_groupe WHERE user_id = :user_id AND groupe_id = :groupe_id";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['user_id' => $userId, 'groupe_id' => $groupId]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // aadad membres groupe
    public function countMembers(int $groupId): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM membre_groupe WHERE groupe_id = :id");
        $stmt->execute(['id' => $groupId]);
        return (int)$stmt->fetchColumn();
    }

    // tzid abd mail w nom de groupe
    public function addMemberByEmailAndGroupName(string $email, string $groupName): bool {
        $query = "INSERT INTO membre_groupe (user_id, groupe_id)
                  VALUES (
                      (SELECT id FROM users WHERE email = :email LIMIT 1),
                      (SELECT id FROM groupe WHERE nom = :groupName LIMIT 1)
                  )";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['email' => $email, 'groupName' => $groupName]);
        } catch (PDOException $e) {
            error_log("Group Assignment Error: " . $e->getMessage());
            return false;
        }
    }

    // creer groupe avec moderateur
    public function createGroupWithModEmail(string $nom, ?string $modEmail = null): bool {
        try {
            if (!empty($modEmail)) {
                $query = "INSERT INTO groupe (nom, moderateur_id)
                          VALUES (:nom, (SELECT id FROM users WHERE email = :email LIMIT 1))";
                $stmt = $this->conn->prepare($query);
                return $stmt->execute(['nom' => $nom, 'email' => $modEmail]);
            } else {
                $query = "INSERT INTO groupe (nom) VALUES (:nom)";
                $stmt = $this->conn->prepare($query);
                return $stmt->execute(['nom' => $nom]);
            }
        } catch (PDOException $e) {
            error_log("Group Creation Error: " . $e->getMessage());
            return false;
        }
    }

    //tous noms de groupes
    public function fetchAllGroupNames(): array {
        try {
            $stmt = $this->conn->query("SELECT nom FROM groupe ORDER BY nom ASC");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    // delete group
    public function delete(int $id): bool {
        try {
            $stmt = $this->conn->prepare("DELETE FROM groupe WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    // Retirer membre de groupe
    public function removeMemberByEmailAndGroupName(string $email, string $groupName): bool {
        $query = "DELETE FROM membre_groupe
                  WHERE user_id   = (SELECT id FROM users WHERE email = :email LIMIT 1)
                  AND   groupe_id = (SELECT id FROM groupe WHERE nom   = :groupName LIMIT 1)";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute(['email' => $email, 'groupName' => $groupName]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
