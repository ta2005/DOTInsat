<?php

require_once BASE_PATH . '/app/Repositories/EtudiantRepository.php';

class NotesController
{
    public function __construct(private PDO $pdo) {}


    public function calculMoyenne(): void
    {   $etudiantRepo = new EtudiantRepository($this->pdo);
        $config = require BASE_PATH . '/config/etudiant.php';
        $filiereRaw = strtoupper(trim($_SESSION['filiere'] ?? ''));
        $filiereRaw = preg_replace('/-.*$/', '', $filiereRaw);
        $filiere = rtrim($filiereRaw, '0123456789');
        $niveau  = (int)substr($filiereRaw, strlen($filiere));
        $anneeAffichage = $_SESSION['annee'] ?? '';
        $pdo = $this->pdo;

        require BASE_PATH . '/views/pages/student/examens/calculator.php';
    }

  
    public function mesNotes(): void
    {
        $etudiantRepo = new EtudiantRepository($this->pdo);
        $config = require BASE_PATH . '/config/etudiant.php';
        $pdo = $this->pdo;

        require BASE_PATH . '/views/pages/student/notes.php';
    }
}
