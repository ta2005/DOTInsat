<?php
// app/Controllers/DispatcherController.php

require_once BASE_PATH . '/app/Controllers/EtudiantController.php';
require_once BASE_PATH . '/app/Controllers/AdminController.php';
require_once BASE_PATH . '/app/Controllers/ProfessorController.php';

class DispatcherController
{
    public function __construct(private PDO $pdo) {}

    /*
    |--------------------------------------------------------------------------
    | GET ?page=home — redirige vers le bon controller selon le rôle
    |--------------------------------------------------------------------------
    */
    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? '';

        match ($role) {
            ROLE_ADMIN      => (new AdminController($this->pdo))->home(),
            ROLE_PROFESSEUR => (new ProfessorController($this->pdo))->home(),
            default         => (new EtudiantController($this->pdo))->home(),
        };
    }
}
