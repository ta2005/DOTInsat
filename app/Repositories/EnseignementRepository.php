<?php

class EnseignementRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // retourne les enseignements d'un prof 
    public function getByProfesseur(int $profId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                id,
                nom,
                date_debut,
                (niveau_scolaire_info).classe   AS classe,
                (niveau_scolaire_info).annee    AS annee,
                (niveau_scolaire_info).niveau   AS niveau,
                (niveau_scolaire_info).filiere  AS filiere
            FROM enseignement
            WHERE professeur_id = :prof_id
            ORDER BY nom
        ");
        $stmt->execute([':prof_id' => $profId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // retourne juste les noms des enseignements d'un prof (pour le select de la page de gestion)
    public function getNomsByProfesseur(int $profId): array
    {
        $stmt = $this->db->prepare("
            SELECT nom
            FROM enseignement
            WHERE professeur_id = :prof_id
            ORDER BY nom
        ");
        $stmt->execute([':prof_id' => $profId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

// retourne les stats d'un prof (nombre de controles, meilleure note, moyenne)
    public function getStatsByProfesseur(int $profId, ?int $enseignementId = null): array
    {
        $sql = "
            SELECT
                COUNT(c.id)                        AS nb_controles,
                MAX(c.note)                        AS meilleure_note,
                ROUND(AVG(c.note)::NUMERIC, 2)     AS moyenne
            FROM controle c
            JOIN enseignement e ON e.id = c.enseignement_id
            WHERE e.professeur_id = :prof_id
        ";

        $params = [':prof_id' => $profId];

        if ($enseignementId !== null) {
            $sql .= " AND e.id = :ens_id";
            $params[':ens_id'] = $enseignementId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
