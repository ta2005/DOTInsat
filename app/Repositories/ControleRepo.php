<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\IControleRepository;
use PDO;
use PDOException;

class ControleRepository extends Repo implements IControleRepository
{
    protected static string $tableName = 'controle'; // Matches your literal table name
    protected static string $entityClass = \App\Entity\Controle::class;

    public function findExamById(int $examId): ?array
    {
        // Query matching your explicit lowercase column choices: id, note, type, statut, format, enseignement_id
        $query = "SELECT id, type, statut, format, enseignement_id FROM controle WHERE id = :id LIMIT 1";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $examId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? $row : null;
        } catch (PDOException $e) {
            error_log("PostgreSQL Error in findExamById: " . $e->getMessage());
            return null;
        }
    }

    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool
    {
        // Updates your 'note' column and changes 'statut' using your 'statut_note' enum value ('CORRIGE')
        // Assumes your table includes an etudiant_id field to separate student copies
        $query = "UPDATE controle 
                  SET note = :grade, statut = 'CORRIGE'::statut_note 
                  WHERE id = :exam_id AND etudiant_id = :student_id";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                'grade' => $grade,
                'exam_id' => $examId,
                'student_id' => $studentId
            ]);
        } catch (PDOException $e) {
            error_log("PostgreSQL Update Error in saveStudentGrade: " . $e->getMessage());
            return false;
        }
    }
}