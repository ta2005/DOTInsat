<?php

require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';
require_once BASE_PATH . '/app/Repositories/EnseignementRepository.php';

class ProfessorController
{
    private ReclamationRepository $repo;
    private EnseignementRepository $ensRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo    = new ReclamationRepository($this->pdo);
        $this->ensRepo = new EnseignementRepository($this->pdo);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=prof-reclamations
    |--------------------------------------------------------------------------
    */
    public function reclamations(): void
    {
        $reclamations = $this->repo->getAll();

        $config = [
            'nav' => [
                ['label' => 'Home',         'href' => '/?page=home'],
                ['label' => 'Blog',         'href' => '/?page=forum'],
                ['label' => 'Examens',      'href' => '/?page=examens-prof'],
                ['label' => 'Réclamations', 'href' => '/?page=prof-reclamations', 'active' => true],
            ],
        ];

        require BASE_PATH . '/views/pages/professor/prof_reclamations.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=examens-prof
    |--------------------------------------------------------------------------
    */
    public function examens(): void
    {
        require BASE_PATH . '/views/pages/professor/examens-prof.php';
    }
}
