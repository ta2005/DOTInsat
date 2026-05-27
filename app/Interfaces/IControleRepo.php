<?php
// app/Interfaces/IControleRepo.php

declare(strict_types=1);

require_once BASE_PATH . '/app/Interfaces/IRepo.php';

interface IControleRepo
{
    public function findExamById(int $examId): ?array;
    public function saveStudentGrade(int $studentId, int $examId, float $grade): bool;
    public function fetchExamsByCourse(int $enseignementId): array;
    public function createExam(array $data): int;
}