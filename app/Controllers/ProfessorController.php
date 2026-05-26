*<?php

require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';

class ProfessorController
{
    private ReclamationRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new ReclamationRepository($this->pdo);
    }

    // GET ?page=prof-reclamations
    public function reclamations(): void
    {
        $reclamations = $this->repo->getAll();

        $config = [
            'nav' => [
                ['label' => 'Home',         'href' => '/?page=home'],
                ['label' => 'Blog',         'href' => '/?page=forum'],
                ['label' => 'Examens',      'href' => '/?page=qcm-create'],
                ['label' => 'Réclamations', 'href' => '/?page=prof-reclamations', 'active' => true],
            ],
        ];

        require BASE_PATH . '/views/pages/professor/prof_reclamations.php';
    }
}