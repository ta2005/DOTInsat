<?php
declare(strict_types=1);

interface IControleRepository extends IRepo
{
    public function findExamById(int $examId): ?array;

    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool;
}
