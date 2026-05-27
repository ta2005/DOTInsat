<?php
declare(strict_types=1);

namespace App\Repositories;
use App\Interfaces\IControleRepo;
use PDO;
use PDOException;
use App\Entity\Controle;
class ControleRepo extends Repo implements IControleRepo
{
    protected static string $tableName = 'controle';
    protected static string $entityClass = Controle::class;

    /**
     * Maps a raw DB row to a Controle entity.
     */
    protected function mapToEntity(array $row): Controle
    {
        return new Controle(
            (int) $row['enseignement_id'],
            $row['type'],
            $row['statut'],
            $row['format'],
            isset($row['note']) ? (float) $row['note'] : null,
            (int) $row['id']
        );
    }

    /**
     * Find a single controle record by ID.
     * Returns a raw assoc array (for the grading pipeline to inspect metadata).
     */
    public function findExamById(int $examId): ?array
    {
        if ($this->conn === null) {
            return [
                'id' => $examId,
                'type' => 'EXAM',
                'statut' => 'EN_ATTENTE',
                'format' => 'QCM',
                'enseignement_id' => 1
            ];
        }
        $query = 'SELECT id, type, statut, format, enseignement_id
                  FROM controle
                  WHERE id = :id
                  LIMIT 1';
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['id' => $examId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('ControleRepository::findExamById error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Save the graded score for a student on a specific exam.
     * Updates note and sets statut to CORRIGE.
     *
     * NOTE: This assumes the controle table has an etudiant_id column
     * tracking per-student copies of the exam row.
     */
    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool
    {
        if ($this->conn === null) {
            return true;
        }
        $query = "UPDATE controle
                  SET note   = :grade,
                      statut = 'CORRIGE'::statut_note
                  WHERE id           = :exam_id
                    AND etudiant_id  = :student_id";
        try {
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                'grade' => $grade,
                'exam_id' => $examId,
                'student_id' => $studentId,
            ]);
        } catch (PDOException $e) {
            error_log('ControleRepository::saveStudentGrade error: ' . $e->getMessage());
            return false;
        }
    }


    public function fetchExamsByCourse(int $enseignementId): array
    {
        $query = "SELECT id, note, type, statut, format, enseignement_id 
              FROM controle 
              WHERE enseignement_id = :course_id 
              ORDER BY id DESC";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute(['course_id' => $enseignementId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Database Error [fetchExamsByCourse]: " . $e->getMessage());
            return [];
        }
    }

    public function createExam(array $data): int
    {
        $query = "INSERT INTO controle (type, statut, format, enseignement_id) 
              VALUES (:type::type_controle, 'EN_ATTENTE'::statut_note, :format::format, :enseignement_id) 
              RETURNING id";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                'type' => $data['type'], // 'DS', 'EXAM', 'TP'
                'format' => $data['format'], // 'QCM', 'MIX', 'NON_QCM'
                'enseignement_id' => (int) $data['enseignement_id']
            ]);
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Database Error [createExam]: " . $e->getMessage());
            return 0;
        }
    }
}