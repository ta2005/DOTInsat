<?php
// app/Repositories/NotificationRepository.php

require_once BASE_PATH . '/app/Repositories/Repository.php';

class NotificationRepository extends Repository
{
    private function resolveUserId(string $destinataire): ?int
    {
        if (!$this->isConnected()) return null;

        if ($destinataire === 'admin') {
            $row = $this->db->query("SELECT id FROM admin LIMIT 1")->fetch();
            return $row ? (int)$row['id'] : null;
        }

        if (str_starts_with($destinataire, 'etudiant_')) {
            return (int)substr($destinataire, strlen('etudiant_'));
        }

        return null;
    }

    public function ajouter(string $destinataire, string $message): bool
    {
        if (!$this->isConnected()) return false;

        $userId = $this->resolveUserId($destinataire);
        if (!$userId) return false;

        $stmt = $this->db->prepare(
            "INSERT INTO notification (message, user_id, createur_id) VALUES (?, ?, 1)"
        );
        return $stmt->execute([$message, $userId]);
    }

    public function getPour(int $userId): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->prepare("
            SELECT id::TEXT, message, lue AS lu, date_creation AS date
            FROM notification
            WHERE user_id = ?
            ORDER BY date_creation DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marquerLue(int $id): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("UPDATE notification SET lue = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
