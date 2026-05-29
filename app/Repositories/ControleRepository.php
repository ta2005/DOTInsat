<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Interfaces/IControleRepo.php';
require_once BASE_PATH . '/app/Repositories/Repository.php';

class ControleRepository extends Repository implements IControleRepo
{

    public function fetchById(string $id): ?object
    {
        $stmt = $this->db->prepare("
            SELECT * FROM controle WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (object) $row : null;
    }

    public function fetchAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM controle ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $id): bool
    {
        $examId = (int) $id;

        
        $exam = $this->findExamById($examId);
        if (!$exam) {
            return false;
        }

        $ensId = (int) $exam['enseignement_id'];
        $type = $exam['type'];

        
        $stmt = $this->db->prepare("
            SELECT id FROM controle 
            WHERE enseignement_id = :ens_id AND type = :type
        ");
        $stmt->execute([
            ':ens_id' => $ensId,
            ':type' => $type
        ]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $ids = array_map(fn($r) => (int) $r['id'], $rows);

        if (empty($ids)) {
            return false;
        }

     
        $inQuery = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->db->prepare("DELETE FROM reclamation WHERE controle_id IN ($inQuery)");
        $stmt->execute($ids);

       
        $stmt = $this->db->prepare("DELETE FROM controle WHERE id IN ($inQuery)");
        $stmt->execute($ids);

        
        $storagePath = __DIR__ . '/../../storage/qcm_keys';
        $filePath = sprintf('%s/exam_%d.json', $storagePath, $examId);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        return true;
    }

 
    public function findExamById(int $examId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.id,
                c.type,
                c.statut,
                c.format,
                c.note,
                c.enseignement_id
            FROM controle c
            WHERE c.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $examId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool
    {
        
        $exam = $this->findExamById($examId);
        if (!$exam) {
            return false;
        }

        $ensId = (int) $exam['enseignement_id'];
        $type = $exam['type'];

        
        $stmt = $this->db->prepare("
            SELECT id FROM controle 
            WHERE etudiant_id = :etudiant_id 
              AND enseignement_id = :ens_id 
              AND type = :type
            LIMIT 1
        ");
        $stmt->execute([
            ':etudiant_id' => $studentId,
            ':ens_id' => $ensId,
            ':type' => $type
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            
            $stmt = $this->db->prepare("
                UPDATE controle
                SET
                    note   = :note,
                    statut = 'CORRIGE',
                    format = :format
                WHERE
                    id = :id
            ");
            $stmt->execute([
                ':note' => $grade,
                ':format' => $exam['format'],
                ':id' => $existing['id']
            ]);
            return true;
        } else {
            
            $stmt = $this->db->prepare("
                INSERT INTO controle (
                    note,
                    type,
                    statut,
                    format,
                    enseignement_id,
                    etudiant_id
                ) VALUES (
                    :note,
                    :type,
                    'CORRIGE',
                    :format,
                    :ens_id,
                    :etudiant_id
                )
            ");
            $stmt->execute([
                ':note' => $grade,
                ':type' => $type,
                ':format' => $exam['format'],
                ':ens_id' => $ensId,
                ':etudiant_id' => $studentId
            ]);
            return $stmt->rowCount() > 0;
        }
    }

    public function fetchExamsByCourse(int $enseignementId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.id,
                c.type,
                c.statut,
                c.format,
                c.note,
                c.etudiant_id,
                u.cin as student_cin,
                u.nom as student_nom,
                u.prenom as student_prenom,
                u.email as student_email
            FROM controle c
            LEFT JOIN etudiant e ON c.etudiant_id = e.id
            LEFT JOIN users u ON e.id = u.id
            WHERE c.enseignement_id = :ens_id
            ORDER BY c.type ASC, c.id ASC
        ");
        $stmt->execute([':ens_id' => $enseignementId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchStudentsByCourse(int $enseignementId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                u.id as student_id,
                u.cin,
                u.nom,
                u.prenom,
                u.email
            FROM etudiant e
            JOIN users u ON e.id = u.id
            JOIN enseignement ens ON ens.niveau_scolaire_info = e.niveau_scolaire_info
            WHERE ens.id = :ens_id
            ORDER BY u.nom ASC, u.prenom ASC
        ");
        $stmt->execute([':ens_id' => $enseignementId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createExam(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO controle (
                type,
                statut,
                format,
                enseignement_id
            ) VALUES (
                :type,
                'EN_ATTENTE',
                :format,
                :enseignement_id
            )
            RETURNING id
        ");

        $stmt->execute([
            ':type' => $data['type'],
            ':format' => $data['format'],
            ':enseignement_id' => (int) $data['enseignement_id'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function updateExamFormat(int $examId, string $format): bool
    {
        $exam = $this->findExamById($examId);
        if (!$exam) {
            return false;
        }

        $stmt = $this->db->prepare("
            UPDATE controle
            SET format = :format
            WHERE enseignement_id = :ens_id AND type = :type
        ");
        $stmt->execute([
            ':format' => $format,
            ':ens_id' => $exam['enseignement_id'],
            ':type' => $exam['type']
        ]);

        return $stmt->rowCount() > 0;
    }

    public function modifyStudentGradeAndStatus(int $studentId, int $examId, ?float $grade, string $status): bool
    {
        $exam = $this->findExamById($examId);
        if (!$exam) {
            return false;
        }

        $ensId = (int) $exam['enseignement_id'];
        $type = $exam['type'];

        $stmt = $this->db->prepare("
            SELECT id FROM controle 
            WHERE etudiant_id = :etudiant_id 
              AND enseignement_id = :ens_id 
              AND type = :type
            LIMIT 1
        ");
        $stmt->execute([
            ':etudiant_id' => $studentId,
            ':ens_id' => $ensId,
            ':type' => $type
        ]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $stmt = $this->db->prepare("
                UPDATE controle
                SET
                    note   = :note,
                    statut = :statut,
                    format = :format
                WHERE
                    id = :id
            ");
            $stmt->execute([
                ':note' => $grade,
                ':statut' => $status,
                ':format' => $exam['format'],
                ':id' => $existing['id']
            ]);
            return true;
        } else {
            $stmt = $this->db->prepare("
                INSERT INTO controle (
                    note,
                    type,
                    statut,
                    format,
                    enseignement_id,
                    etudiant_id
                ) VALUES (
                    :note,
                    :type,
                    :statut,
                    :format,
                    :ens_id,
                    :etudiant_id
                )
            ");
            $stmt->execute([
                ':note' => $grade,
                ':type' => $type,
                ':statut' => $status,
                ':format' => $exam['format'],
                ':ens_id' => $ensId,
                ':etudiant_id' => $studentId
            ]);
            return $stmt->rowCount() > 0;
        }
    }

    public function fetchMasterExamsByProf(int $profId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT c.id, c.type, c.format, c.enseignement_id, ens.nom as course_name
            FROM controle c
            JOIN enseignement ens ON c.enseignement_id = ens.id
            WHERE ens.professeur_id = :prof_id 
              AND c.etudiant_id IS NULL 
              AND c.format IN ('QCM', 'MIX')
            ORDER BY ens.nom ASC, c.type ASC, c.id ASC
        ");
        $stmt->execute([':prof_id' => $profId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}