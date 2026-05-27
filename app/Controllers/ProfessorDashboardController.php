<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\IControleRepo;
use PDO;

class ProfessorDashboardController
{
    private PDO $db;
    private IControleRepo $controleRepo;

    public function __construct(PDO $db, IControleRepo $controleRepo)
    {
        $this->db = $db;
        $this->controleRepo = $controleRepo;
    }

    /**
     * Renders the primary workspace.
     */
    public function index(): void
    {
        // Hardcoded session profile ID for development context (e.g., Professor ID = 2)
        $profId = 3;

        // 1. Fetch all courses taught by this professor
        $stmt = $this->db->prepare("SELECT id, nom, niveau_scolaire_info FROM enseignement WHERE professeur_id = ?");
        $stmt->execute([$profId]);
        $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Determine currently selected course (defaults to the first one)
        $selectedCourseId = isset($_GET['course_id']) ? (int) $_GET['course_id'] : ($courses[0]['id'] ?? 0);

        // 3. Fetch related exams for the active course selection context
        $exams = [];
        if ($selectedCourseId > 0) {
            $exams = $this->controleRepo->fetchExamsByCourse($selectedCourseId);
        }

        // Send parameters over to your nested view layer
        require_once __DIR__ . '/../../views/pages/professor/dashboard.php';
    }

    /**
     * Handles the form submission for creating a new exam.
     */
    public function handleCreateExam(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            return;

        $type = $_POST['type'] ?? '';
        $format = $_POST['format'] ?? '';
        $courseId = (int) ($_POST['enseignement_id'] ?? 0);

        if (!$type || !$format || $courseId === 0) {
            header("Location: /professor/dashboard?error=missing_fields");
            return;
        }

        // Insert into database via repository layer
        $newExamId = $this->controleRepo->createExam([
            'type' => $type,
            'format' => $format,
            'enseignement_id' => $courseId
        ]);

        if ($newExamId === 0) {
            header("Location: /professor/dashboard?course_id={$courseId}&error=db_failure");
            return;
        }

        // Route redirection rules based on selected format
        if ($format === 'QCM') {
            header("Location: /professor/qcm/create?exam_id={$newExamId}");
        } elseif ($format === 'MIX') {
            // Send to dashboard with flag prompting confirmation choice to build QCM component
            header("Location: /professor/dashboard?course_id={$courseId}&prompt_mix_qcm={$newExamId}");
        } else {
            // 'NON_QCM' format goes back to dashboard silently
            header("Location: /professor/dashboard?course_id={$courseId}&success=exam_created");
        }
    }
}