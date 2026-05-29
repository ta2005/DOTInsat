<?php
// app/Repositories/ReclamationRepository.php

require_once BASE_PATH . '/app/Repositories/Repository.php';

class ReclamationRepository extends Repository
{

    public function getAll(): array
    {
        if (!$this->isConnected()) return [];

        $role = $_SESSION['user_role'] ?? '';

        if ($role === 'professeur') {
            return $this->getAllForProf((int)($_SESSION['user_id'] ?? 0));
        }

        $stmt = $this->db->query("
            SELECT
                r.id,
                r.etudiant_id,
                u.nom,
                u.prenom,
                u.cin                        AS num,
                e.id                         AS enseignement_id,
                e.nom                        AS matiere_nom,
                c.type::TEXT                 AS type_eval,
                c.type::TEXT                 AS type_eval_label,
                c.note                       AS note_actuelle,
                r.message                    AS commentaire,
                r.statut::TEXT               AS statut,
                r.date_creation              AS date_soumission,
                r.note_nouvelle,
                r.raison_refus,
                pu.nom || ' ' || pu.prenom   AS prof_nom
            FROM reclamation r
            JOIN etudiant     et ON et.id = r.etudiant_id
            JOIN users         u ON u.id  = et.id
            JOIN controle      c ON c.id  = r.controle_id
            JOIN enseignement  e ON e.id  = c.enseignement_id
            JOIN professeur    p ON p.id  = e.professeur_id
            JOIN users        pu ON pu.id = p.id
            ORDER BY r.date_creation DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAllForProf(int $profId): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->prepare("
            SELECT
                r.id,
                r.etudiant_id,
                u.nom,
                u.prenom,
                u.cin                        AS num,
                e.id                         AS enseignement_id,
                e.nom                        AS matiere_nom,
                c.type::TEXT                 AS type_eval,
                c.type::TEXT                 AS type_eval_label,
                c.note                       AS note_actuelle,
                r.message                    AS commentaire,
                r.statut::TEXT               AS statut,
                r.date_creation              AS date_soumission,
                r.note_nouvelle,
                r.raison_refus,
                pu.nom || ' ' || pu.prenom   AS prof_nom
            FROM reclamation r
            JOIN etudiant     et ON et.id = r.etudiant_id
            JOIN users         u ON u.id  = et.id
            JOIN controle      c ON c.id  = r.controle_id
            JOIN enseignement  e ON e.id  = c.enseignement_id
            JOIN professeur    p ON p.id  = e.professeur_id
            JOIN users        pu ON pu.id = p.id
            WHERE p.id = :prof_id
            ORDER BY r.date_creation DESC
        ");

        $stmt->execute([':prof_id' => $profId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getById(int $id): ?array
    {
        if (!$this->isConnected()) return null;

        $stmt = $this->db->prepare("
            SELECT
                r.id,
                r.etudiant_id,
                u.nom,
                u.prenom,
                u.cin                        AS num,
                e.id                         AS enseignement_id,
                e.nom                        AS matiere_nom,
                c.type::TEXT                 AS type_eval,
                c.type::TEXT                 AS type_eval_label,
                c.note                       AS note_actuelle,
                r.message                    AS commentaire,
                r.statut::TEXT               AS statut,
                r.date_creation              AS date_soumission,
                r.note_nouvelle,
                r.raison_refus,
                pu.nom || ' ' || pu.prenom   AS prof_nom
            FROM reclamation r
            JOIN etudiant     et ON et.id = r.etudiant_id
            JOIN users         u ON u.id  = et.id
            JOIN controle      c ON c.id  = r.controle_id
            JOIN enseignement  e ON e.id  = c.enseignement_id
            JOIN professeur    p ON p.id  = e.professeur_id
            JOIN users        pu ON pu.id = p.id
            WHERE r.id = ?
        ");

        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    //matiere etudiants connectée
    public function getMatieres(): array
    {
        if (!$this->isConnected()) return [];

        $etudiantId = (int)($_SESSION['user_id'] ?? 0);

        $stmt = $this->db->prepare("
            SELECT DISTINCT ON (m.id)
                m.id,
                m.nom_matiere                AS nom,
                m.filiere,
                m.niveau,
                m.semestre,
                m.coefficient,
                m.types_controle,
                u.nom || ' ' || u.prenom     AS prof
            FROM matieres m
            JOIN etudiant    et ON (et.niveau_scolaire_info).filiere = m.filiere
                                AND (et.niveau_scolaire_info).niveau = m.niveau::TEXT
            JOIN enseignement en ON en.matiere_id = m.id
            JOIN professeur   p  ON p.id          = en.professeur_id
            JOIN users        u  ON u.id          = p.id
            JOIN controle      c  ON c.enseignement_id = en.id
                                 AND c.etudiant_id     = :etudiant_id
            WHERE et.id = :etudiant_id2
            ORDER BY m.id, en.id
        ");

        $stmt->execute([
            ':etudiant_id'  => $etudiantId,
            ':etudiant_id2' => $etudiantId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    // matire etudiant connecté avec les notes (ds, examen, tp)
    public function getMatieresAvecNotes(int $etudiantId): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->prepare("
            SELECT
                e.id,
                e.nom,
                pu.nom || ' ' || pu.prenom                       AS prof,
                MAX(c.note) FILTER (WHERE c.type::TEXT = 'DS')   AS ds,
                MAX(c.note) FILTER (WHERE c.type::TEXT = 'EXAM') AS examen,
                MAX(c.note) FILTER (WHERE c.type::TEXT = 'TP')   AS tp
            FROM enseignement e
            JOIN professeur p  ON p.id  = e.professeur_id
            JOIN users pu      ON pu.id = p.id
            LEFT JOIN controle c ON c.enseignement_id = e.id
                                AND c.etudiant_id     = :etudiant_id
            GROUP BY e.id, e.nom, pu.nom, pu.prenom
            ORDER BY e.nom
        ");

        $stmt->execute([':etudiant_id' => $etudiantId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'id'     => (string)$r['id'],
            'nom'    => $r['nom'],
            'prof'   => $r['prof'],
            'ds'     => $r['ds']     !== null ? (float)$r['ds']     : null,
            'examen' => $r['examen'] !== null ? (float)$r['examen'] : null,
            'tp'     => $r['tp']     !== null ? (float)$r['tp']     : null,
        ], $rows);
    }

    //pour lister les reclamations d'un etudiant connecté
    public function getMesReclamations(int $etudiantId): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->prepare("
            SELECT
                r.id,
                e.nom                        AS matiere_nom,
                c.type::TEXT                 AS type_eval,
                c.note                       AS note_actuelle,
                r.note_nouvelle,
                r.message                    AS commentaire,
                r.statut::TEXT               AS statut,
                r.raison_refus,
                r.date_creation              AS date_soumission
            FROM reclamation r
            JOIN controle     c  ON c.id  = r.controle_id
            JOIN enseignement e  ON e.id  = c.enseignement_id
            WHERE r.etudiant_id = :etudiant_id
            ORDER BY r.date_creation DESC
        ");

        $stmt->execute([':etudiant_id' => $etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    // types de controles disponibles pour une matiere donnée (pour un etudiant donné)
    public function getTypesByMatiere(int $matiereId, int $etudiantId): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->prepare("
            SELECT
                c.id         AS controle_id,
                c.type::TEXT AS type,
                c.note       AS note
            FROM controle c
            JOIN enseignement e ON e.id = c.enseignement_id
            WHERE e.matiere_id  = :matiere_id
              AND c.etudiant_id = :etudiant_id
            ORDER BY c.type
        ");

        $stmt->execute([
            ':matiere_id'  => $matiereId,
            ':etudiant_id' => $etudiantId,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    // pour créer une nouvelle réclamation (étudiant connecté)
    public function create(array $data): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("
            INSERT INTO reclamation (message, statut, controle_id, etudiant_id)
            VALUES (:message, 'EN_ATTENTE', :controle_id, :etudiant_id)
        ");

        return $stmt->execute([
            ':message'     => $data['message'],
            ':controle_id' => $data['controle_id'],
            ':etudiant_id' => $data['etudiant_id'],
        ]);
    }

    // pour supprimer une réclamation (admin ou prof connecté)
    public function delete(int $id): bool
    {
        if (!$this->isConnected()) return false;

        return $this->db->prepare("DELETE FROM reclamation WHERE id = ?")
                        ->execute([$id]);
    }

    // pour mettre à jour le statut d'une réclamation (admin ou prof connecté)

    public function updateStatut(int $id, string $statut): bool
    {
        if (!$this->isConnected()) return false;

        return $this->db->prepare(
            "UPDATE reclamation SET statut = ?::statut_reclamation WHERE id = ?"
        )->execute([$statut, $id]);
    }


    // pour approuver une réclamation par le prof connecté (met à jour aussi la note dans controle)
    public function approuverParProf(int $id, float $nouvelleNote): bool
    {
        if (!$this->isConnected()) return false;

        $ok = $this->db->prepare("
            UPDATE reclamation
            SET statut        = 'ACCEPTEE_PAR_LE_PROFESSEUR'::statut_reclamation,
                note_nouvelle = :note
            WHERE id = :id
        ")->execute([':note' => $nouvelleNote, ':id' => $id]);

        if (!$ok) return false;

        return $this->db->prepare("
            UPDATE controle c
            SET    note = :note
            FROM   reclamation r
            WHERE  r.id = :reclamation_id
              AND  c.id = r.controle_id
        ")->execute([':note' => $nouvelleNote, ':reclamation_id' => $id]);
    }


    // pour refuser une réclamation par le prof connecté (enregistre aussi la raison du refus)
    public function refuserParProf(int $id): bool
    {
        if (!$this->isConnected()) return false;

        return $this->db->prepare("
            UPDATE reclamation
            SET statut       = 'REFUSEE_PAR_LE_PROFESSEUR'::statut_reclamation
            WHERE id = :id
        ")->execute([ ':id' => $id]);
    }



    // bech na3tiw les labels w les classes css mtaa les statuts 
    public function getStatutLabels(): array
{
    if (!$this->isConnected()) return [];

    $stmt = $this->db->query("
        SELECT statut::TEXT AS statut, label, classe
        FROM statut_reclamation_label
        ORDER BY statut
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // indexer par statut pour un accès plus facile dans les vues
    return array_column($rows, null, 'statut');
}
}
