<?php

class HomeController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        $role = $_SESSION['user_role'] ?? '';

        $config = match ($role) {
            ROLE_PROFESSEUR => require BASE_PATH . '/config/enseignant.php',
            ROLE_ADMIN      => require BASE_PATH . '/config/administrateur.php',
            default         => require BASE_PATH . '/config/etudiant.php',
        };

        require BASE_PATH . '/views/pages/home.php';
    }
}