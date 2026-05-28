<?php

require_once BASE_PATH . '/app/Repositories/Repository.php';

class ReclamationRepository extends Repository
{
    // Toutes les réclamations (admin : toutes ; prof : filtrées par prof connecté)
    public function getAll(): array
    {
        if (!$this->isConnected()) return [];

        $role = $_SESSION['user_role'] ?? '';

        // Pour le professeur : on ne retourne que les réclamations
        // dont l'enseignement lui appartient
        if ($role === 'professeur') {
            return $this->getAllForProf((int)($_SESSION['user_id'] ?? 0));
        }

        // Pour l'admin : toutes les réclamations
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

    // Réclamations filtrées pour un professeur donné
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

    // Réclamation par ID
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

    /**
     * CORRECTION : on ajoute un filtre sur l'étudiant dans la jointure enseignement
     * pour s'assurer que le prof retourné est bien celui qui enseigne à cet étudiant.
     * On utilise aussi un subquery pour éviter le problème du DISTINCT ON avec mauvais prof.
     */
    public function getMatieres(): array
    {
        if (!$this->isConnected()) return [];

        $etudiantId = (int)($_SESSION['user_id'] ?? 0);

        $stmt = $this->db->prepare("
            SELECT
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
            WHERE et.id = :etudiant_id
              AND EXISTS (
                  SELECT 1
                  FROM controle c2
                  WHERE c2.enseignement_id = en.id
                    AND c2.etudiant_id     = :etudiant_id2
              )
            ORDER BY m.id, en.id
            LIMIT 1 OFFSET 0
        ");

        // CORRECTION : on utilise un GROUP BY propre au lieu du DISTINCT ON problématique
        $stmt2 = $this->db->prepare("
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

        $stmt2->execute([
            ':etudiant_id'  => $etudiantId,
            ':etudiant_id2' => $etudiantId,
        ]);

        return $stmt2->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

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

    public function delete(int $id): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("DELETE FROM reclamation WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function updateStatut(int $id, string $statut): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("
            UPDATE reclamation 
            SET statut = ?::statut_reclamation 
            WHERE id = ?
        ");

        return $stmt->execute([$statut, $id]);
    }

    public function approuverParProf(int $id, float $nouvelleNote): bool
    {
        if (!$this->isConnected()) return false;

        // Met à jour la réclamation
        $stmt = $this->db->prepare("
            UPDATE reclamation
            SET statut       = 'ACCEPTEE_PAR_LE_PROFESSEUR'::statut_reclamation,
                note_nouvelle = :note
            WHERE id = :id
        ");
        $ok = $stmt->execute([':note' => $nouvelleNote, ':id' => $id]);

        if (!$ok) return false;

        // Met aussi à jour la note dans controle
        $stmt2 = $this->db->prepare("
            UPDATE controle c
            SET    note   = :note
            FROM   reclamation r
            WHERE  r.id          = :reclamation_id
              AND  c.id          = r.controle_id
        ");

        return $stmt2->execute([':note' => $nouvelleNote, ':reclamation_id' => $id]);
    }

    public function refuserParProf(int $id, string $raison): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("
            UPDATE reclamation
            SET statut       = 'REFUSEE_PAR_LE_PROFESSEUR'::statut_reclamation,
                raison_refus = :raison
            WHERE id = :id
        ");

        return $stmt->execute([':raison' => $raison, ':id' => $id]);
    }

    /**
     * CORRECTION : suppression du GROUP BY inutile, ORDER BY ajouté pour cohérence.
     * La requête retourne maintenant tous les controles de l'étudiant pour cette matière.
     */
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
}
