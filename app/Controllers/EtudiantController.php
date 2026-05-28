<?php
// app/Controllers/EtudiantController.php

require_once BASE_PATH . '/app/Repositories/EtudiantRepository.php';

class EtudiantController
{
    public function __construct(private PDO $pdo) {}

    /*
    |--------------------------------------------------------------------------
    | GET ?page=home (étudiant)
    |--------------------------------------------------------------------------
    */
    public function home(): void
    {
        $config = $this->buildEtudiantConfig();
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
    | Construction de la config étudiant
    | public — réutilisée par NotesController
    |--------------------------------------------------------------------------
    */
    public function buildEtudiantConfig(): array
    {
        $etuId = (int)($_SESSION['user_id'] ?? 0);
        $repo  = new EtudiantRepository($this->pdo);

        $profil     = $etuId ? $repo->getProfil($etuId) : null;
        $classe     = $profil['classe'] ?? 'GL3-2';
        $nomComplet = $profil
            ? trim($profil['prenom'] . ' ' . $profil['nom'])
            : 'Étudiant';

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
