<?php

require_once BASE_PATH . '/app/Repositories/Repository.php';

class DemandeRepository extends Repository
{
    // Toutes les demandes avec infos étudiant
    public function getAll(): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->query("
            SELECT
                d.id,
                u.nom,
                u.prenom,
                d.message,
                d.type::TEXT        AS type,
                d.type::TEXT        AS type_label,
                d.statut::TEXT      AS statut,
                d.date_creation     AS date_soumission
            FROM demande d
            JOIN users u ON u.id = d.user_id
            ORDER BY d.date_creation DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer les valeurs ENUM type_demande directement depuis la BD
    public function getTypesDemande(): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->query("
            SELECT unnest(enum_range(NULL::type_demande))::TEXT AS value
        ");

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Labels lisibles pour chaque valeur ENUM
        $labels = [
            'ATTESTATION_DE_INSCRIPTION' => "Attestation d'inscription",
            'ATTESTATION_DE_PRESENCE'    => "Attestation de présence",
            'FEUILLES_DE_STAGE'          => "Feuilles de stage",
            'AUTRE'                      => "Autre",
        ];

        return array_map(fn($row) => [
            'value' => $row['value'],
            'label' => $labels[$row['value']]
                ?? ucfirst(strtolower(str_replace('_', ' ', $row['value']))),
        ], $rows);
    }

    // Créer une demande
    public function create(array $data): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("
            INSERT INTO demande (message, type, statut, user_id, admin_id)
            VALUES (:message, :type, 'EN_ATTENTE', :user_id, 1)
        ");

        return $stmt->execute([
            ':message' => $data['message'],
            ':type'    => $data['type'],
            ':user_id' => $data['user_id'],
        ]);
    }

    // Supprimer
    public function delete(int $id): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("DELETE FROM demande WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Changer le statut
    public function updateStatut(int $id, string $statut): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("UPDATE demande SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }
}