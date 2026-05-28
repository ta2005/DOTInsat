<?php
declare(strict_types=1);

require_once BASE_PATH . '/app/Repositories/ControleRepository.php';
require_once BASE_PATH . '/app/Repositories/EnseignementRepository.php';
require_once BASE_PATH . '/app/Services/QcmGradingService.php';

class QcmController
{
    private QcmGradingService $gradingService;
    private ControleRepository $controleRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->gradingService = new QcmGradingService();
        $this->controleRepo = new ControleRepository($this->pdo);
    }


    public function examens(): void
    {
        require BASE_PATH . '/views/pages/professor/examens-prof.php';
    }


    public function dashboard(): void
    {
        $profId = (int) ($_SESSION['user_id'] ?? 0);
        $ensRepo = new EnseignementRepository($this->pdo);

        $courses = $ensRepo->getByProfesseur($profId);
        $selectedCourseId = (int) ($_GET['course_id'] ?? 0);
        $exams = [];

        if ($selectedCourseId > 0) {
            $examsRaw = $this->controleRepo->fetchExamsByCourse($selectedCourseId);
            $enrolledStudents = $this->controleRepo->fetchStudentsByCourse($selectedCourseId);

            // Auto-heal missing master exams if student grades exist without a parent master exam row
            $typesFormatsWithMaster = [];
            foreach ($examsRaw as $row) {
                if ($row['etudiant_id'] === null) {
                    $key = strtoupper($row['type']) . '|' . strtoupper($row['format'] ?? '');
                    $typesFormatsWithMaster[$key] = true;
                }
            }

            $inserted = false;
            foreach ($examsRaw as $row) {
                if ($row['etudiant_id'] !== null) {
                    $key = strtoupper($row['type']) . '|' . strtoupper($row['format'] ?? '');
                    if (!isset($typesFormatsWithMaster[$key])) {
                        $stmt = $this->pdo->prepare("
                            INSERT INTO controle (type, statut, format, enseignement_id, etudiant_id)
                            VALUES (:type, 'EN_ATTENTE', :format, :ens_id, NULL)
                        ");
                        $stmt->execute([
                            ':type'   => $row['type'],
                            ':format' => $row['format'],
                            ':ens_id' => $selectedCourseId
                        ]);
                        $typesFormatsWithMaster[$key] = true;
                        $inserted = true;
                    }
                }
            }

            if ($inserted) {
                $examsRaw = $this->controleRepo->fetchExamsByCourse($selectedCourseId);
            }

            // 1. Collect all master exams (rows where etudiant_id IS NULL)
            $masterExams = [];
            foreach ($examsRaw as $row) {
                if ($row['etudiant_id'] === null) {
                    $masterExams[(int) $row['id']] = [
                        'id' => (int) $row['id'],
                        'type' => strtoupper($row['type']),
                        'format' => strtoupper($row['format']),
                        'statut' => $row['statut'],
                        'students' => []
                    ];
                }
            }

            // 2. Associate student grade rows (where etudiant_id IS NOT NULL) to the matching master exam by type and format
            foreach ($examsRaw as $row) {
                if ($row['etudiant_id'] !== null) {
                    $matchedMasterId = null;
                    foreach ($masterExams as $mId => $mExam) {
                        if ($mExam['type'] === strtoupper($row['type'])) {
                            if ($mExam['format'] === strtoupper($row['format'])) {
                                $matchedMasterId = $mId;
                                break;
                            }
                            $matchedMasterId = $mId;
                        }
                    }

                    if ($matchedMasterId !== null) {
                        $masterExams[$matchedMasterId]['students'][] = [
                            'id' => (int) $row['id'],
                            'student_id' => (int) $row['etudiant_id'],
                            'cin' => $row['student_cin'],
                            'nom' => $row['student_nom'],
                            'prenom' => $row['student_prenom'],
                            'email' => $row['student_email'],
                            'note' => $row['note'],
                            'statut' => $row['statut']
                        ];
                    }
                }
            }

            // 3. For each master exam, merge enrolled students so all students are visible
            foreach ($masterExams as &$exam) {
                $actualStudents = [];
                $studentGrades = [];
                foreach ($exam['students'] as $s) {
                    $studentGrades[$s['student_id']] = $s;
                }

                foreach ($enrolledStudents as $es) {
                    $sid = $es['student_id'];
                    if (isset($studentGrades[$sid])) {
                        $actualStudents[] = $studentGrades[$sid];
                    } else {
                        $actualStudents[] = [
                            'id' => null,
                            'student_id' => $sid,
                            'cin' => $es['cin'],
                            'nom' => $es['nom'],
                            'prenom' => $es['prenom'],
                            'email' => $es['email'],
                            'note' => null,
                            'statut' => '--'
                        ];
                    }
                }
                $exam['students'] = $actualStudents;

                // Calibrate the master exam status based on student correction statuses
                if (!empty($exam['students'])) {
                    $allCorrige = true;
                    foreach ($exam['students'] as $s) {
                        if ($s['statut'] === 'EN_ATTENTE' || $s['statut'] === '--') {
                            $allCorrige = false;
                            break;
                        }
                    }
                    $exam['statut'] = $allCorrige ? 'CORRIGE' : 'EN_ATTENTE';
                }
            }
            unset($exam);

            $exams = array_values($masterExams);
        }

        require BASE_PATH . '/views/pages/professor/qcm-dashboard.php';
    }


    public function create(): void
    {
        require BASE_PATH . '/views/pages/professor/qcm-create.php';
    }


    public function scan(): void
    {
        $profId = (int) ($_SESSION['user_id'] ?? 0);
        $exams = $this->controleRepo->fetchMasterExamsByProf($profId);
        foreach ($exams as &$exam) {
            $exam['students'] = $this->controleRepo->fetchStudentsByCourse((int) $exam['enseignement_id']);
        }
        unset($exam);

        require BASE_PATH . '/views/pages/professor/qcm-scan.php';
    }


    public function saveTemplate(): void
    {
        $this->requirePost();

        $payload = $this->readJsonBody();

        if (empty($payload['exam_id']) || empty($payload['grading_matrix'])) {
            $this->jsonError('Champs requis manquants : exam_id ou grading_matrix.', 400);
            return;
        }

        $examId = (int) $payload['exam_id'];

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
            'message' => "Clé maître sauvegardée pour l'examen #{$examId}.",
            'file' => "exam_{$examId}.json",
            'exam_format' => $exam['format'],
        ]);
    }


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

        $examId = (int) $payload['exam_id'];
        $studentId = (int) $payload['student_id'];
        $answers = $payload['student_answers'];

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
            'exam_id' => $examId,
            'student_id' => $studentId,
            'db_saved' => $saved,
            'message' => $saved
                ? "Note {$finalGrade} enregistrée avec succès."
                : "Note calculée mais non enregistrée en base.",
        ]);
    }


    public function getTemplate(): void
    {
        $examId = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 0;

        if ($examId <= 0) {
            $this->jsonError('Paramètre exam_id manquant ou invalide.', 400);
            return;
        }

        $filePath = $this->gradingService->keyFilePath($examId);

        if (!file_exists($filePath)) {
            $this->jsonError("Modèle de correction introuvable pour l'examen #{$examId}. Veuillez d'abord configurer les réponses.", 404);
            return;
        }

        $data = json_decode(file_get_contents($filePath), true);
        $this->jsonSuccess($data);
    }


    public function createExam(): void
    {
        $this->requirePost();

        $enseignementId = $_POST['enseignement_id'] ?? null;
        $type = $_POST['type'] ?? null;
        $format = $_POST['format'] ?? null;

        if (empty($enseignementId) || empty($type) || empty($format)) {
            $this->jsonError('Champs requis manquants : enseignement_id, type ou format.', 400);
            return;
        }

        $data = [
            'type' => $type,
            'format' => $format,
            'enseignement_id' => (int) $enseignementId,
        ];

        $examId = $this->controleRepo->createExam($data);

        $this->jsonSuccess([
            'exam_id' => $examId,
            'format' => $format,
            'enseignement_id' => (int) $enseignementId,
            'message' => "Examen créé avec succès.",
        ]);
    }

    public function modifyExam(): void
    {
        $this->requirePost();

        $examId = (int) ($_POST['exam_id'] ?? 0);
        $format = $_POST['format'] ?? '';

        if ($examId <= 0 || !in_array($format, ['QCM', 'MIX', 'NON_QCM'], true)) {
            $this->jsonError('Données invalides.');
            return;
        }

        $success = $this->controleRepo->updateExamFormat($examId, $format);
        if ($success) {
            $this->jsonSuccess(['message' => 'Format mis à jour avec succès.']);
        } else {
            $this->jsonError('Impossible de mettre à jour le format.');
        }
    }

    public function modifyStudentGrade(): void
    {
        $this->requirePost();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $examId = (int) ($_POST['exam_id'] ?? 0);
        $status = $_POST['statut'] ?? '';
        $noteRaw = $_POST['note'] ?? '';

        if ($studentId <= 0 || $examId <= 0 || !in_array($status, ['EN_ATTENTE', 'CORRIGE', 'VERIFIE', 'CONTESTE'], true)) {
            $this->jsonError('Données de requête invalides.');
            return;
        }

        $grade = null;
        if ($noteRaw !== '') {
            $grade = (float) $noteRaw;
            if ($grade < 0 || $grade > 20) {
                $this->jsonError('La note doit être comprise entre 0 et 20.');
                return;
            }
        }

        $success = $this->controleRepo->modifyStudentGradeAndStatus($studentId, $examId, $grade, $status);
        if ($success) {
            $this->jsonSuccess(['message' => 'Note et statut mis à jour avec succès.']);
        } else {
            $this->jsonError('Impossible de mettre à jour la note ou le statut.');
        }
    }

    public function deleteExam(): void
    {
        $examId = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 0;

        if ($examId <= 0) {
            $this->jsonError('Paramètre exam_id manquant ou invalide.', 400);
            return;
        }

        $exam = $this->controleRepo->findExamById($examId);
        if ($exam === null) {
            $this->jsonError("Aucun contrôle trouvé avec l'ID {$examId}.", 404);
            return;
        }

        $ensId = (int) $exam['enseignement_id'];

        $success = $this->controleRepo->delete((string) $examId);

        if ($success) {
            header("Location: /?page=qcm-dashboard&course_id={$ensId}");
            exit;
        } else {
            $this->jsonError('Impossible de supprimer l\'examen.', 500);
        }
    }


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
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            $this->jsonError('Corps JSON invalide : ' . json_last_error_msg(), 400);
            exit;
        }

        return $data;
    }

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
    }
}