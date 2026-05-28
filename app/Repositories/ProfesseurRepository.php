<?php
// app/Repositories/ProfesseurRepository.php

class ProfesseurRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Retourne tous les professeurs avec leurs enseignements
     * (classes + matières), filtrables par recherche textuelle.
     */
    public function getAllWithEnseignements(?string $search = null): array
    {
        $sql = "
            SELECT
                u.id,
                u.cin,
                u.nom,
                u.prenom,
                u.email,
                COALESCE(
                    JSON_AGG(
                        JSON_BUILD_OBJECT(
                            'enseignement_id',  e.id,
                            'enseignement_nom', e.nom,
                            'classe',           (e.niveau_scolaire_info).classe,
                            'filiere',          (e.niveau_scolaire_info).filiere,
                            'niveau',           (e.niveau_scolaire_info).niveau,
                            'matiere',          m.nom_matiere,
                            'coefficient',      m.coefficient
                        ) ORDER BY e.nom
                    ) FILTER (WHERE e.id IS NOT NULL),
                    '[]'::json
                ) AS enseignements
            FROM users u
            JOIN professeur p ON p.id = u.id
            LEFT JOIN enseignement e ON e.professeur_id = p.id
            LEFT JOIN matieres m ON m.id = e.matiere_id
        ";

        $params = [];

        if (!empty($search)) {
            $sql .= "
            WHERE
                u.nom    ILIKE :search
                OR u.prenom ILIKE :search
                OR u.email  ILIKE :search
            ";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= "
            GROUP BY u.id, u.cin, u.nom, u.prenom, u.email
            ORDER BY u.nom, u.prenom
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            $row['enseignements'] = json_decode($row['enseignements'], true) ?? [];
        }

        return $rows;
    }

    /**
     * Retourne un professeur par son id (avec ses enseignements).
     */
    public function getById(int $id): ?array
    {
        $results = $this->getAllWithEnseignements();
        foreach ($results as $prof) {
            if ((int)$prof['id'] === $id) {
                return $prof;
            }
        }
        return null;
    }

    /**
     * Crée un professeur (insère dans users + professeur).
     */
    public function create(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO users (cin, nom, prenom, email, mot_passe)
                VALUES (:cin, :nom, :prenom, :email, :mot_passe)
                RETURNING id
            ");
            $stmt->execute([
                ':cin'       => $data['cin']       ?? null,
                ':nom'       => $data['nom'],
                ':prenom'    => $data['prenom'],
                ':email'     => $data['email'],
                ':mot_passe' => password_hash($data['mot_passe'] ?? 'changeme123', PASSWORD_DEFAULT),
            ]);

            $userId = $stmt->fetchColumn();

            $this->db->prepare("INSERT INTO professeur (id) VALUES (:id)")
                     ->execute([':id' => $userId]);

            $this->db->commit();
            return true;

        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Met à jour les infos d'un professeur.
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE users
            SET
                cin    = :cin,
                nom    = :nom,
                prenom = :prenom,
                email  = :email
            WHERE id = :id
        ");

        return $stmt->execute([
            ':cin'    => $data['cin']    ?? null,
            ':nom'    => $data['nom'],
            ':prenom' => $data['prenom'],
            ':email'  => $data['email'],
            ':id'     => $id,
        ]);
    }

    /**
     * Supprime un professeur (cascade via FK).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
