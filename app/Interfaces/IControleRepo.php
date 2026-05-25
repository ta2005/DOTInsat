<?php
declare(strict_types=1);

namespace App\Interfaces;

interface IControleRepo extends IRepo
{

    public function findExamById(int $examId): ?array;

    /**
     * Saves the calculated grade directly into the 'note' column of the controle table.
     */
    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool;
    public function fetchExamsByCourse(int $enseignementId): array;
    public function createExam(array $data): int;
}