<?php

class HomeController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // ─────────────────────────────────────────────
    // HOME
    // ─────────────────────────────────────────────
    public function index()
    {
        $role = $_SESSION['user_role'] ?? '';

        $config = match ($role) {

            ROLE_PROFESSEUR =>
                require BASE_PATH . '/config/enseignant.php',

            ROLE_ADMIN =>
                require BASE_PATH . '/config/administrateur.php',

            default =>
                require BASE_PATH . '/config/etudiant.php',
        };

        require BASE_PATH . '/views/pages/home.php';
    }

    // ─────────────────────────────────────────────
    // PAGE EXAMENS
    // ─────────────────────────────────────────────
    public function examens()
    {
        $config = require BASE_PATH . '/config/etudiant.php';

        require BASE_PATH . '/views/pages/student/examens/index.php';
    }

    // ─────────────────────────────────────────────
    // CALCUL MOYENNE
    // ─────────────────────────────────────────────
    public function calculMoyenne()
    {
        $config = require BASE_PATH . '/config/etudiant.php';

        /*
        |--------------------------------------------------------------------------
        | Données étudiant
        |--------------------------------------------------------------------------
        */

        $filiere = strtoupper(
            trim($_SESSION['filiere'] ?? 'GL')
        );

        $niveau = (int) (
            $_SESSION['annee'] ?? 2
        );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $filieresAutorisees = [
            'GL',
            'RT',
            'IIA',
            'IMI'
        ];

        if (!in_array($filiere, $filieresAutorisees)) {

            die(
                "Filière non supportée : " .
                htmlspecialchars($filiere)
            );
        }

        if ($niveau < 1 || $niveau > 5) {

            die(
                "Niveau non supporté : " .
                htmlspecialchars($niveau)
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Variables accessibles dans la vue
        |--------------------------------------------------------------------------
        */

        $pdo = $this->pdo;

        /*
        |--------------------------------------------------------------------------
        | Vue unique dynamique
        |--------------------------------------------------------------------------
        */

        require BASE_PATH .
            '/views/pages/student/examens/calculator.php';
    }

    // ─────────────────────────────────────────────
    // MES NOTES
    // ─────────────────────────────────────────────
    public function mesNotes()
    {
        $config = require BASE_PATH . '/config/etudiant.php';

        require BASE_PATH . '/views/pages/student/notes.php';
    }
}