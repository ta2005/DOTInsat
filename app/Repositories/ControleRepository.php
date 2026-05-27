<?php
// app/Repositories/ControleRepository.php

declare(strict_types=1);

require_once BASE_PATH . '/app/Interfaces/IControleRepo.php';
require_once BASE_PATH . '/app/Repositories/Repository.php';

class ControleRepository extends Repository implements IControleRepo
{
    /*
    |--------------------------------------------------------------------------
    | IRepo — méthodes génériques
    |--------------------------------------------------------------------------
    */

    public function fetchById(string $id): ?object
    {
        $stmt = $this->db->prepare("
            SELECT * FROM controle WHERE id = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? (object)$row : null;
    }

    public function fetchAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM controle ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(string $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM controle WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | IControleRepo — méthodes métier
    |--------------------------------------------------------------------------
    */

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
        $stmt = $this->db->prepare("
            UPDATE controle
            SET
                note   = :note,
                statut = 'CORRIGE'
            WHERE
                etudiant_id     = :etudiant_id
                AND enseignement_id = :enseignement_id
        ");

        $stmt->execute([
            ':note'            => $grade,
            ':etudiant_id'     => $studentId,
            ':enseignement_id' => $examId,
        ]);

        return $stmt->rowCount() > 0;
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
                u.nom        AS etudiant_nom,
                u.prenom     AS etudiant_prenom
            FROM controle c
            LEFT JOIN etudiant e ON e.id = c.etudiant_id
            LEFT JOIN users    u ON u.id = e.id
            WHERE c.enseignement_id = :ens_id
            ORDER BY c.type ASC, c.id ASC
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
            ':type'            => $data['type'],
            ':format'          => $data['format'],
            ':enseignement_id' => (int)$data['enseignement_id'],
        ]);

        return (int)$stmt->fetchColumn();
    }
}