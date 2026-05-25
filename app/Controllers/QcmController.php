<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\IControleRepo;
use App\Services\QcmGradingService;

class QcmController
{
    private IControleRepo $controleRepo;
    private QcmGradingService $gradingService;

    public function __construct(IControleRepo $controleRepo, QcmGradingService $gradingService)
    {
        $this->controleRepo = $controleRepo;
        $this->gradingService = $gradingService;
    }

    /**
     * Processes browser scanning payload and commits grades to PostgreSQL 'controle' table.
     */
    public function processScannedAnswers(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid HTTP request context.']);
            return;
        }

        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput, true);

        if (!$payload || empty($payload['exam_id']) || empty($payload['student_id']) || !isset($payload['student_answers'])) {
            echo json_encode(['success' => false, 'message' => 'Malformed data payload received.']);
            return;
        }

        // Explicitly cast to integer types to match your PostgreSQL SERIAL keys
        $examId = (int) $payload['exam_id'];
        $studentId = (int) $payload['student_id'];
        $studentAnswers = (array) $payload['student_answers'];

        // 1. Fetch check using our refactored query
        $examMeta = $this->controleRepo->findExamById($examId);
        if (!$examMeta) {
            echo json_encode(['success' => false, 'message' => 'Target exam configuration record not found.']);
            return;
        }

        // 2. Validate format using your literal 'format' enum values ('QCM', 'MIX', 'NON_QCM')
        if ($examMeta['format'] === 'NON_QCM') {
            echo json_encode(['success' => false, 'message' => 'This evaluation profile requires manual input.']);
            return;
        }

        // 3. Compute score via grading logic helper service
        // Pass the string version of the ID for file path naming lookups: exam_{id}.json
        $calculatedScore = $this->gradingService->calculateScore((int) $examId, $studentAnswers);

        if ($calculatedScore === null) {
            echo json_encode(['success' => false, 'message' => 'Missing master answer key file layout on server disk.']);
            return;
        }

        // 4. Update the 'note' column inside table 'controle'
        $dbWriteSuccess = $this->controleRepo->saveStudentGrade($studentId, $examId, $calculatedScore);

        if ($dbWriteSuccess) {
            echo json_encode([
                'success' => true,
                'message' => 'Grade computed and automatically committed to system!',
                'data' => [
                    'student_id' => $studentId,
                    'final_grade' => $calculatedScore,
                    'format' => $examMeta['format']
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to execute database record update transaction.']);
        }
    }
}