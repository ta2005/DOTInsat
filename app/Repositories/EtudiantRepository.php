<?php


class EtudiantRepository
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

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

 function getGroupes(): array
    {
        $stmt = $this->db->query("
            SELECT DISTINCT
                (e.niveau_scolaire_info).filiere AS filiere,
                (e.niveau_scolaire_info).niveau  AS niveau,
                (e.niveau_scolaire_info).classe  AS classe
            FROM etudiant e
            WHERE (e.niveau_scolaire_info).filiere IS NOT NULL
            ORDER BY filiere, niveau, classe
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function getByFiliereEtClasse(string $filiere, string $classe): array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.cin,
                u.nom,
                u.prenom,
                u.email,
                (e.niveau_scolaire_info).classe  AS classe,
                (e.niveau_scolaire_info).filiere AS filiere,
                (e.niveau_scolaire_info).niveau  AS niveau,
                (e.niveau_scolaire_info).annee   AS annee
            FROM etudiant e
            JOIN users u ON u.id = e.id
            WHERE (e.niveau_scolaire_info).filiere = :filiere
              AND (e.niveau_scolaire_info).classe  = :classe
            ORDER BY u.nom, u.prenom
        ");

        $stmt->execute([
            ':filiere' => $filiere,
            ':classe'  => $classe,
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }


    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id,
                u.cin,
                u.nom,
                u.prenom,
                u.email,
                (e.niveau_scolaire_info).classe  AS classe,
                (e.niveau_scolaire_info).filiere AS filiere,
                (e.niveau_scolaire_info).niveau  AS niveau,
                (e.niveau_scolaire_info).annee   AS annee
            FROM etudiant e
            JOIN users u ON u.id = e.id
            WHERE u.id = :id
        ");

        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }


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
                ':mot_passe' => password_hash(
                    $data['mot_passe'] ?? 'changeme123',
                    PASSWORD_BCRYPT
                ),
            ]);

            $userId = (int)$stmt->fetchColumn();

            if (!$userId) {
                $this->db->rollBack();
                return false;
            }

            $stmt2 = $this->db->prepare("
                INSERT INTO etudiant (id, niveau_scolaire_info)
                VALUES (
                    :id,
                    ROW(:classe, :annee, :niveau, :filiere)::niveau_scolaire
                )
            ");

            $stmt2->execute([
                ':id'      => $userId,
                ':classe'  => $data['classe'],
                ':annee'   => (int)($data['annee'] ?? date('Y')),
                ':niveau'  => $data['niveau'],
                ':filiere' => $data['filiere'],
            ]);

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('EtudiantRepository::create() — ' . $e->getMessage());
            return false;
        }
    }

 
    public function update(int $id, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                UPDATE users
                SET
                    cin    = :cin,
                    nom    = :nom,
                    prenom = :prenom,
                    email  = :email
                WHERE id = :id
            ");

            $stmt->execute([
                ':cin'    => $data['cin']    ?? null,
                ':nom'    => $data['nom'],
                ':prenom' => $data['prenom'],
                ':email'  => $data['email'],
                ':id'     => $id,
            ]);

            $stmt2 = $this->db->prepare("
                UPDATE etudiant
                SET niveau_scolaire_info =
                    ROW(:classe, :annee, :niveau, :filiere)::niveau_scolaire
                WHERE id = :id
            ");

            $stmt2->execute([
                ':classe'  => $data['classe'],
                ':annee'   => (int)($data['annee'] ?? date('Y')),
                ':niveau'  => $data['niveau'],
                ':filiere' => $data['filiere'],
                ':id'      => $id,
            ]);

            $this->db->commit();
            return true;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('EtudiantRepository::update() — ' . $e->getMessage());
            return false;
        }
    }


    public function delete(int $id): bool
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            return $stmt->execute([':id' => $id]);

        } catch (\Throwable $e) {
            error_log('EtudiantRepository::delete() — ' . $e->getMessage());
            return false;
        }
    }

   
    public function countByFiliere(): array
    {
        $stmt = $this->db->query("
            SELECT
                (e.niveau_scolaire_info).filiere AS filiere,
                COUNT(*)                         AS total
            FROM etudiant e
            GROUP BY filiere
            ORDER BY filiere
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
