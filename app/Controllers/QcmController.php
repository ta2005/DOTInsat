<?php
declare(strict_types=1);

/**
 * QcmController
 *
 * Handles two JSON API endpoints consumed by the professor's QCM pages:
 *
 *  POST /api/qcm/save-template   → saves the answer-key blueprint to storage
 *  POST /api/qcm/process-scan    → scores a student's submitted answers and
 *                                   persists the grade to the database
 */
class QcmController {
    private QcmGradingService    $gradingService;
    private ControleRepository   $controleRepo;

    public function __construct(?PDO $pdo) {
        $this->gradingService = new QcmGradingService();
        $this->controleRepo   = new ControleRepository($pdo);
    }

    // ------------------------------------------------------------------ //
    //  POST /api/qcm/save-template
    // ------------------------------------------------------------------ //

    /**
     * Receives the professor's answer-key matrix from qcm-builder.js and
     * persists it as a JSON file in storage/qcm_keys/exam_{id}.json.
     *
     * Expected JSON body:
     * {
     *   "exam_id": 14,
     *   "total_questions": 20,
     *   "choices_per_question": 4,
     *   "grading_matrix": {
     *     "q1": { "correct_choice": "B", "weight": 1 },
     *     ...
     *   }
     * }
     */
    public function saveTemplate(): void {
        $this->requirePost();

        $payload = $this->readJsonBody();

        // Validate required fields
        if (empty($payload['exam_id']) || empty($payload['grading_matrix'])) {
            $this->jsonError('Missing required fields: exam_id or grading_matrix.', 400);
            return;
        }

        $examId = (int) $payload['exam_id'];

        // Verify the exam actually exists in the DB before saving the key
        $exam = $this->controleRepo->findExamById($examId);
        if ($exam === null) {
            $this->jsonError("Controle with ID {$examId} does not exist in the database.", 404);
            return;
        }

        // Only QCM/MIX-format exams should have answer keys
        if (!in_array($exam['format'], ['QCM', 'MIX'], true)) {
            $this->jsonError(
                "Controle #{$examId} has format '{$exam['format']}'; only QCM and MIX exams support answer keys.",
                422
            );
            return;
        }

        $saved = $this->gradingService->saveTemplate($payload);

        if (!$saved) {
            $this->jsonError('Failed to write the template file to storage. Check server permissions.', 500);
            return;
        }

        $this->jsonSuccess([
            'message'    => "Master key blueprint saved for exam #{$examId}.",
            'file'       => "exam_{$examId}.json",
            'exam_format' => $exam['format'],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  POST /api/qcm/process-scan
    // ------------------------------------------------------------------ //

    /**
     * Receives the OMR-extracted student answers from qcm-scanner.js,
     * calculates the grade via QcmGradingService, and persists it to the DB.
     *
     * Expected JSON body:
     * {
     *   "exam_id":        14,
     *   "student_id":     1002,
     *   "student_answers": { "q1": "B", "q2": "A", ... }
     * }
     */
    public function processScan(): void {
        $this->requirePost();

        $payload = $this->readJsonBody();

        // Validate required fields
        if (
            empty($payload['exam_id'])
            || empty($payload['student_id'])
            || empty($payload['student_answers'])
            || !is_array($payload['student_answers'])
        ) {
            $this->jsonError('Missing required fields: exam_id, student_id, or student_answers.', 400);
            return;
        }

        $examId    = (int) $payload['exam_id'];
        $studentId = (int) $payload['student_id'];
        $answers   = $payload['student_answers'];

        // Calculate score using the stored master-key
        $finalGrade = $this->gradingService->calculateScore($examId, $answers);

        if ($finalGrade === null) {
            $this->jsonError(
                "No master key found for exam #{$examId}. Please generate and save the template first.",
                404
            );
            return;
        }

        // Persist the grade to the database
        $saved = $this->controleRepo->saveStudentGrade($studentId, $examId, $finalGrade);

        if (!$saved) {
            // Grade calculated but DB write failed — still return the score so the
            // professor can see it; log the failure separately.
            error_log("QcmController::processScan – DB write failed for student {$studentId}, exam {$examId}.");
        }

        $this->jsonSuccess([
            'final_grade' => $finalGrade,
            'exam_id'     => $examId,
            'student_id'  => $studentId,
            'db_saved'    => $saved,
            'message'     => $saved
                ? "Grade {$finalGrade} saved successfully."
                : "Grade calculated but could not be saved to the database.",
        ]);
    }

    // ------------------------------------------------------------------ //
    //  GET /api/qcm/get-template
    // ------------------------------------------------------------------ //
    public function getTemplate(): void {
        $examId = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
        if ($examId <= 0) {
            $this->jsonError('Missing or invalid exam_id parameter.', 400);
            return;
        }
        $filePath = $this->gradingService->keyFilePath($examId);
        if (!file_exists($filePath)) {
            // Check for fallbacks in offline testing mode
            $files = glob(dirname($filePath) . '/exam_*.json');
            if (!empty($files)) {
                $filePath = $files[0];
            } else {
                $this->jsonError("Template for exam #{$examId} not found.", 404);
                return;
            }
        }
        $data = json_decode(file_get_contents($filePath), true);
        $this->jsonSuccess($data);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers
    // ------------------------------------------------------------------ //

    /** Abort with 405 if the request is not POST. */
    private function requirePost(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed. Use POST.']);
            exit;
        }
    }

    /**
     * Decode the raw JSON request body.
     * Aborts with 400 on malformed JSON.
     */
    private function readJsonBody(): array {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $this->jsonError('Invalid JSON body: ' . json_last_error_msg(), 400);
            exit;
        }

        return $data;
    }

    /** Send a success JSON response and exit. */
    private function jsonSuccess(array $data): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    /** Send an error JSON response and exit. */
    private function jsonError(string $message, int $httpCode = 400): void {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
