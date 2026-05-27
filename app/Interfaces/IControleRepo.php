<?php
<<<<<<< HEAD
// app/Interfaces/IControleRepo.php

declare(strict_types=1);

require_once BASE_PATH . '/app/Interfaces/IRepo.php';

interface IControleRepo
{
    public function findExamById(int $examId): ?array;
=======
declare(strict_types=1);

namespace App\Interfaces;

interface IControleRepo extends IRepo
{

    public function findExamById(int $examId): ?array;

    /**
     * Saves the calculated grade directly into the 'note' column of the controle table.
     */
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool;
    public function fetchExamsByCourse(int $enseignementId): array;
    public function createExam(array $data): int;
}