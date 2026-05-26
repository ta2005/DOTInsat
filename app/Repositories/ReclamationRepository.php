<?php

require_once BASE_PATH . '/app/Repositories/Repository.php';

class ReclamationRepository extends Repository
{
    // Toutes les réclamations avec infos étudiant + matière + prof
    public function getAll(): array
    {
        if (!$this->isConnected()) return [];

        $stmt = $this->db->query("
            SELECT
                r.id,
                r.etudiant_id,
                u.nom,
                u.prenom,
                e.id   AS enseignement_id,
                e.nom  AS matiere_nom,
                r.type_controle::TEXT AS type_eval,
                r.type_controle::TEXT AS type_eval_label,
                r.message             AS commentaire,
                r.statut::TEXT        AS statut,
                r.date_creation       AS date_soumission,
                pu.nom || ' ' || pu.prenom AS prof_nom
            FROM reclamation r
            JOIN etudiant    et ON et.id = r.etudiant_id
            JOIN users        u ON u.id  = et.id
            JOIN enseignement e ON e.id  = r.enseignement_id
            JOIN professeur   p ON p.id  = e.professeur_id
            JOIN users       pu ON pu.id = p.id
            ORDER BY r.date_creation DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Réclamation par ID
    public function getById(int $id): ?array
    {
        if (!$this->isConnected()) return null;

        $stmt = $this->db->prepare("
            SELECT
                r.id, r.etudiant_id,
                u.nom, u.prenom,
                e.id AS enseignement_id, e.nom AS matiere_nom,
                r.type_controle::TEXT AS type_eval,
                r.message AS commentaire,
                r.statut::TEXT AS statut,
                r.date_creation AS date_soumission,
                pu.nom || ' ' || pu.prenom AS prof_nom
            FROM reclamation r
            JOIN etudiant et ON et.id = r.etudiant_id
            JOIN users u     ON u.id  = et.id
            JOIN enseignement e ON e.id = r.enseignement_id
            JOIN professeur p   ON p.id = e.professeur_id
            JOIN users pu       ON pu.id = p.id
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Liste des matières avec note et prof — pour le formulaire étudiant
    public function getMatieres(): array
    {
        if (!$this->isConnected()) {
            // Fallback statique si pas de BDD
            return [
                ['id' => '1', 'nom' => 'Algorithmique',    'prof' => 'Dr. Sonia Trabelsi', 'ds' => 12, 'examen' => 11],
                ['id' => '2', 'nom' => 'Java',             'prof' => 'Dr. Ahmed Ben Ali',  'ds' => 14, 'examen' => 16],
                ['id' => '3', 'nom' => 'Bases de Données', 'prof' => 'Dr. Karim Meddeb',   'ds' => 17, 'examen' => 19],
                ['id' => '4', 'nom' => 'Réseaux',          'prof' => 'Dr. Leila Gharbi',   'ds' => 10, 'examen' => 13],
                ['id' => '5', 'nom' => 'Mathématiques',    'prof' => 'Dr. Nizar Hajji',    'ds' => 15, 'examen' => 14],
            ];
        }

        $etudiantId = $_SESSION['user_id'] ?? 0;

        $stmt = $this->db->prepare("
            SELECT
                e.id::TEXT            AS id,
                e.nom                 AS nom,
                pu.nom || ' ' || pu.prenom AS prof,
                MAX(CASE WHEN c.type::TEXT = 'DS'   THEN c.note END) AS ds,
                MAX(CASE WHEN c.type::TEXT = 'EXAM' THEN c.note END) AS examen
            FROM enseignement e
            JOIN professeur  p  ON p.id  = e.professeur_id
            JOIN users       pu ON pu.id = p.id
            LEFT JOIN controle c ON c.enseignement_id = e.id
                                AND c.etudiant_id = ?
            GROUP BY e.id, e.nom, pu.nom, pu.prenom
            ORDER BY e.nom
        ");
        $stmt->execute([$etudiantId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Créer une réclamation
    public function create(array $data): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("
            INSERT INTO reclamation (message, type_controle, statut, enseignement_id, etudiant_id, admin_id)
            VALUES (:message, :type_controle, 'EN_ATTENTE', :enseignement_id, :etudiant_id, 1)
        ");

        return $stmt->execute([
            ':message'         => $data['message'],
            ':type_controle'   => $data['type_controle'],
            ':enseignement_id' => $data['enseignement_id'],
            ':etudiant_id'     => $data['etudiant_id'],
        ]);
    }

    // Supprimer
    public function delete(int $id): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("DELETE FROM reclamation WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Changer le statut (admin ou prof)
    public function updateStatut(int $id, string $statut): bool
    {
        if (!$this->isConnected()) return false;

        $stmt = $this->db->prepare("UPDATE reclamation SET statut = ? WHERE id = ?");
        return $stmt->execute([$statut, $id]);
    }
}
