<?php
declare(strict_types=1);

<<<<<<< HEAD
require_once BASE_PATH . '/app/Repositories/ControleRepository.php';
require_once BASE_PATH . '/app/Repositories/EnseignementRepository.php';
require_once BASE_PATH . '/app/Services/QcmGradingService.php';

class QcmController
{
    private QcmGradingService  $gradingService;
    private ControleRepository $controleRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->gradingService = new QcmGradingService();
        $this->controleRepo   = new ControleRepository($this->pdo);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=examens-prof
    |--------------------------------------------------------------------------
    */
    public function examens(): void
    {
        require BASE_PATH . '/views/pages/professor/examens-prof.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=qcm-dashboard
    |--------------------------------------------------------------------------
    */
    public function dashboard(): void
    {
        $profId  = (int)($_SESSION['user_id'] ?? 0);
        $ensRepo = new EnseignementRepository($this->pdo);

        $courses          = $ensRepo->getByProfesseur($profId);
        $selectedCourseId = (int)($_GET['course_id'] ?? 0);
        $exams            = [];

        if ($selectedCourseId > 0) {
            $exams = $this->controleRepo->fetchExamsByCourse($selectedCourseId);
        }

        require BASE_PATH . '/views/pages/professor/qcm-dashboard.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=qcm-create
    |--------------------------------------------------------------------------
    */
    public function create(): void
    {
        require BASE_PATH . '/views/pages/professor/qcm-create.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=qcm-scan
    |--------------------------------------------------------------------------
    */
    public function scan(): void
    {
        require BASE_PATH . '/views/pages/professor/qcm-scan.php';
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=api-create-exam
    |--------------------------------------------------------------------------
    */
    public function createExam(): void
    {
        $type           = $_POST['type']             ?? '';
        $format         = $_POST['format']           ?? '';
        $enseignementId = (int)($_POST['enseignement_id'] ?? 0);

        if (!$type || !$format || $enseignementId <= 0) {
            header('Location: /?page=qcm-dashboard');
            exit;
        }

        $examId = $this->controleRepo->createExam([
            'type'            => $type,
            'format'          => $format,
            'enseignement_id' => $enseignementId,
        ]);

        if ($format === 'MIX') {
            header("Location: /?page=qcm-dashboard&course_id={$enseignementId}&prompt_mix_qcm={$examId}");
        } else {
            header("Location: /?page=qcm-dashboard&course_id={$enseignementId}");
        }
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=api-save-template
    |--------------------------------------------------------------------------
    */
    public function saveTemplate(): void
    {
        $this->requirePost();

        $payload = $this->readJsonBody();

        if (empty($payload['exam_id']) || empty($payload['grading_matrix'])) {
            $this->jsonError('Champs requis manquants : exam_id ou grading_matrix.', 400);
            return;
        }

        $examId = (int)$payload['exam_id'];

        $exam = $this->controleRepo->findExamById($examId);
        if ($exam === null) {
            $this->jsonError("Aucun contrôle trouvé avec l'ID {$examId}.", 404);
            return;
        }

        if (!in_array($exam['format'], ['QCM', 'MIX'], true)) {
            $this->jsonError(
                "Le contrôle #{$examId} est de format '{$exam['format']}' ; seuls QCM et MIX acceptent une clé de correction.",
                422
            );
            return;
        }

        $saved = $this->gradingService->saveTemplate($payload);

        if (!$saved) {
            $this->jsonError('Impossible d\'écrire le fichier de clé. Vérifiez les permissions du serveur.', 500);
            return;
        }

        $this->jsonSuccess([
            'message'     => "Clé maître sauvegardée pour l'examen #{$examId}.",
            'file'        => "exam_{$examId}.json",
            'exam_format' => $exam['format'],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=api-process-scan
    |--------------------------------------------------------------------------
    */
    public function processScan(): void
    {
        $this->requirePost();

        $payload = $this->readJsonBody();

        if (
            empty($payload['exam_id'])
            || empty($payload['student_id'])
            || empty($payload['student_answers'])
            || !is_array($payload['student_answers'])
        ) {
            $this->jsonError('Champs requis manquants : exam_id, student_id ou student_answers.', 400);
            return;
        }

        $examId    = (int)$payload['exam_id'];
        $studentId = (int)$payload['student_id'];
        $answers   = $payload['student_answers'];

        $finalGrade = $this->gradingService->calculateScore($examId, $answers);

        if ($finalGrade === null) {
            $this->jsonError(
                "Aucune clé maître trouvée pour l'examen #{$examId}. Générez et sauvegardez le modèle d'abord.",
                404
            );
            return;
        }

        $saved = $this->controleRepo->saveStudentGrade($studentId, $examId, $finalGrade);

        if (!$saved) {
            error_log("QcmController::processScan — échec DB pour étudiant {$studentId}, examen {$examId}.");
        }

        $this->jsonSuccess([
            'final_grade' => $finalGrade,
            'exam_id'     => $examId,
            'student_id'  => $studentId,
            'db_saved'    => $saved,
            'message'     => $saved
                ? "Note {$finalGrade} enregistrée avec succès."
                : "Note calculée mais non enregistrée en base.",
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=api-get-template
    |--------------------------------------------------------------------------
    */
    public function getTemplate(): void
    {
        $examId = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

        if ($examId <= 0) {
            $this->jsonError('Paramètre exam_id manquant ou invalide.', 400);
            return;
        }

        $filePath = $this->gradingService->keyFilePath($examId);

        if (!file_exists($filePath)) {
            $files = glob(dirname($filePath) . '/exam_*.json');
            if (!empty($files)) {
                $filePath = $files[0];
            } else {
                $this->jsonError("Modèle introuvable pour l'examen #{$examId}.", 404);
                return;
            }
        }

        $data = json_decode(file_get_contents($filePath), true);
        $this->jsonSuccess($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers privés
    |--------------------------------------------------------------------------
    */

    private function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
            exit;
        }
    }

    private function readJsonBody(): array
    {
        $raw  = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $this->jsonError('Corps JSON invalide : ' . json_last_error_msg(), 400);
            exit;
=======
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
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
        }

        $rawInput = file_get_contents('php://input');
        $payload = json_decode($rawInput, true);

<<<<<<< HEAD
    private function jsonSuccess(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    private function jsonError(string $message, int $httpCode = 400): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
=======
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
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
    }
}