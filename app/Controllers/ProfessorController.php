<?php

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
        // Le prof ne voit que les réclamations qui lui sont transmises (ACCEPTEE par admin)
        $reclamations = $this->repo->getAll();

        require BASE_PATH . '/views/pages/professor/reclamations.php';
    }
}
