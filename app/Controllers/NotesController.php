<?php
// app/Controllers/NotesController.php

require_once BASE_PATH . '/app/Controllers/EtudiantController.php';

class NotesController
{
    public function __construct(private PDO $pdo) {}

    /*
    |--------------------------------------------------------------------------
    | GET ?page=calcul-moyenne
    |--------------------------------------------------------------------------
    */
    public function calculMoyenne(): void
    {
        $etudiant = new EtudiantController($this->pdo);
        $config   = $etudiant->buildEtudiantConfig();

        $filiereRaw = strtoupper(trim($_SESSION['filiere'] ?? ''));

        preg_match('/^([A-Z]+)/', $filiereRaw, $mF);
        $filiere = $mF[1] ?? '';

        preg_match('/^[A-Z]+(\d)/', $filiereRaw, $mN);
        $niveau = isset($mN[1]) ? (int)$mN[1] : 0;

        $filieresAutorisees = ['GL', 'RT', 'IIA', 'IMI'];

        if (!in_array($filiere, $filieresAutorisees)) {
            die('Filière non supportée : ' . htmlspecialchars($filiereRaw));
        }

        if ($niveau < 1 || $niveau > 5) {
            die('Niveau non supporté : ' . htmlspecialchars((string)$niveau));
        }

        $pdo = $this->pdo;

        require BASE_PATH . '/views/pages/student/examens/calculator.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=mes-notes
    |--------------------------------------------------------------------------
    */
    public function mesNotes(): void
    {
        $etudiant = new EtudiantController($this->pdo);
        $config   = $etudiant->buildEtudiantConfig();

        $pdo = $this->pdo;

        require BASE_PATH . '/views/pages/student/notes.php';
    }
}
