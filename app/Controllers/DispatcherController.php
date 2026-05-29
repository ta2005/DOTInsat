<?php
// hedha ll contoller ychouf ll role w ywajhek ll page ka admin wla professeur wla etudiant

require_once BASE_PATH . '/app/Controllers/EtudiantController.php';
require_once BASE_PATH . '/app/Controllers/AdminController.php';
require_once BASE_PATH . '/app/Controllers/ProfessorController.php';

class DispatcherController
{
    public function __construct(private PDO $pdo) {}

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? '';

        //taayet ll home mtaa ll controller ll mouneesseb hassb ll role
        match ($role) {
            ROLE_ADMIN      => (new AdminController($this->pdo))->home(),
            ROLE_PROFESSEUR => (new ProfessorController($this->pdo))->home(),
            default         => (new EtudiantController($this->pdo))->home(),
        };
    }
}
