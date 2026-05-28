<?php
// app/Repositories/EtudiantRepository.php

class EtudiantRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /*
    |--------------------------------------------------------------------------
    | Profil étudiant (nom, prénom, classe)
    |--------------------------------------------------------------------------
    */
    public function getProfil(int $etuId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.nom,
                u.prenom,
                (et.niveau_scolaire_info).classe AS classe
            FROM users u
            JOIN etudiant et ON et.id = u.id
            WHERE u.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $etuId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Nombre de notes acquises par l'étudiant
    |--------------------------------------------------------------------------
    */
    public function getNbNotes(int $etuId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(id) AS nb
            FROM controle
            WHERE etudiant_id = :id
              AND note IS NOT NULL
        ");
        $stmt->execute([':id' => $etuId]);

        return (int)($stmt->fetchColumn() ?: 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Dernière note de l'étudiant
    | Retourne ['nom_matiere' => ..., 'note' => ...] ou null
    |--------------------------------------------------------------------------
    */
    public function getDerniereNote(int $etuId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT m.nom_matiere, c.note
            FROM controle c
            JOIN enseignement e ON e.id = c.enseignement_id
            JOIN matieres m     ON m.id = e.matiere_id
            WHERE c.etudiant_id = :id
              AND c.note IS NOT NULL
            ORDER BY c.id DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $etuId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /*
    |--------------------------------------------------------------------------
    | Dernière réclamation de l'étudiant
    | Retourne ['statut' => ...] ou null
    |--------------------------------------------------------------------------
    */
    public function getDerniereReclamation(int $etuId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT statut
            FROM reclamation
            WHERE etudiant_id = :id
            ORDER BY date_creation DESC
            LIMIT 1
        ");
        $stmt->execute([':id' => $etuId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
