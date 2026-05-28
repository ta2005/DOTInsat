<?php
// app/Controllers/HomeController.php

require_once BASE_PATH . '/app/Repositories/EtudiantRepository.php';
require_once BASE_PATH . '/app/Repositories/AdminRepository.php';

class HomeController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=home
    |--------------------------------------------------------------------------
    */
    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? '';

        $adminRepo = new AdminRepository($this->pdo);

        $config = match ($role) {
            ROLE_PROFESSEUR => require BASE_PATH . '/config/enseignant.php',
            ROLE_ADMIN      => require BASE_PATH . '/config/administrateur.php',
            default         => $this->buildEtudiantConfig(),
        };

        require BASE_PATH . '/views/pages/home.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=examens
    |--------------------------------------------------------------------------
    */
    public function examens(): void
    {
        $config = $this->buildEtudiantConfig();
        require BASE_PATH . '/views/pages/student/examens/index.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=calcul-moyenne
    |--------------------------------------------------------------------------
    */
    public function calculMoyenne(): void
    {
        $config = $this->buildEtudiantConfig();

        /*
        |----------------------------------------------------------------------
        | $_SESSION['filiere'] = 'GL3-2'  (classe complète)
        | On extrait filiere et niveau depuis cette valeur uniquement.
        | 'GL3-2'  → filiere='GL'  niveau=3
        | 'IIA4-1' → filiere='IIA' niveau=4
        |----------------------------------------------------------------------
        */
        $filiereRaw = strtoupper(trim($_SESSION['filiere'] ?? ''));

        preg_match('/^([A-Z]+)/', $filiereRaw, $mF);
        $filiere = $mF[1] ?? '';

        preg_match('/^[A-Z]+(\d)/', $filiereRaw, $mN);
        $niveau = isset($mN[1]) ? (int)$mN[1] : 0;

        $filieresAutorisees = ['GL', 'RT', 'IIA', 'IMI'];

        if (!in_array($filiere, $filieresAutorisees)) {
            die("Filière non supportée : " . htmlspecialchars($filiereRaw));
        }

        if ($niveau < 1 || $niveau > 5) {
            die("Niveau non supporté : " . htmlspecialchars((string)$niveau));
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
        $config = $this->buildEtudiantConfig();
        require BASE_PATH . '/views/pages/student/notes.php';
    }

    /*
    |--------------------------------------------------------------------------
    | Construction de la config étudiant via EtudiantRepository
    |--------------------------------------------------------------------------
    */
    private function buildEtudiantConfig(): array
    {
        $etuId = (int)($_SESSION['user_id'] ?? 0);
        $repo  = new EtudiantRepository($this->pdo);

        // Profil
        $profil     = $etuId ? $repo->getProfil($etuId) : null;
        $classe     = $profil['classe'] ?? 'GL3-2';
        $nomComplet = $profil
            ? trim($profil['prenom'] . ' ' . $profil['nom'])
            : 'Étudiant';

        // Stats
        $nbNotes = $etuId ? $repo->getNbNotes($etuId) : 0;

        $derniereNoteRow = $etuId ? $repo->getDerniereNote($etuId) : null;
        $derniereNote    = $derniereNoteRow
            ? $derniereNoteRow['nom_matiere'] . ' — ' . number_format((float)$derniereNoteRow['note'], 2)
            : '—';

        $reclamRow      = $etuId ? $repo->getDerniereReclamation($etuId) : null;
        $derniereReclam = $reclamRow
            ? ucfirst(strtolower(str_replace('_', ' ', $reclamRow['statut'])))
            : '—';

        return require BASE_PATH . '/config/etudiant.php';
    }
}
