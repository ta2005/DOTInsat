<?php
// app/Controllers/ProfessorController.php

require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';
require_once BASE_PATH . '/app/Repositories/EnseignementRepository.php';
require_once BASE_PATH . '/app/Repositories/ProfesseurRepository.php';

class ProfessorController
{
    private ReclamationRepository  $repo;
    private EnseignementRepository $ensRepo;
    private ProfesseurRepository   $profRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo     = new ReclamationRepository($this->pdo);
        $this->ensRepo  = new EnseignementRepository($this->pdo);
        $this->profRepo = new ProfesseurRepository($this->pdo);
    }

 
    public function home(): void
    {
        $profRepo = $this->profRepo;
        $ensRepo  = $this->ensRepo;
        $config   = require BASE_PATH . '/config/enseignant.php';
        require BASE_PATH . '/views/pages/home.php';
    }


    public function reclamations(): void
    {   $profRepo = $this->profRepo;
        $ensRepo  = $this->ensRepo;
        $reclamations = $this->repo->getAll();
        $config       = require BASE_PATH . '/config/enseignant.php';
        
        require BASE_PATH . '/views/pages/professor/prof_reclamations.php';
    }

    public function reclamationAction(): void
    {
        $action = $_POST['action'] ?? '';
        $id     = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Réclamation introuvable.'];
            header('Location: /?page=prof-reclamations');
            exit;
        }

        if ($action === 'prof_approuver') {

            $note = (float)($_POST['nouvelle_note'] ?? -1);

            if ($note < 0 || $note > 20) {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Note invalide (0-20).'];
                header('Location: /?page=prof-reclamations');
                exit;
            }

            $ok = $this->repo->approuverParProf($id, $note);
            $_SESSION['flash'] = $ok
                ? ['type' => 'success', 'msg' => 'Note modifiée avec succès.']
                : ['type' => 'error',   'msg' => 'Erreur lors de la mise à jour.'];

        } elseif ($action === 'prof_refuser') {

            $raison = trim($_POST['raison'] ?? '');

            if ($raison === '') {
                $_SESSION['flash'] = ['type' => 'error', 'msg' => 'La raison du refus est obligatoire.'];
                header('Location: /?page=prof-reclamations');
                exit;
            }

            $ok = $this->repo->refuserParProf($id, $raison);
            $_SESSION['flash'] = $ok
                ? ['type' => 'success', 'msg' => 'Réclamation refusée.']
                : ['type' => 'error',   'msg' => 'Erreur lors du refus.'];

        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Action inconnue.'];
        }

        header('Location: /?page=prof-reclamations');
        exit;
    }

    public function examens(): void
    {   $profRepo = $this->profRepo;
        $ensRepo  = $this->ensRepo;
        $config   = require BASE_PATH . '/config/enseignant.php';
        require BASE_PATH . '/views/layouts/header.php'; 
        require BASE_PATH . '/views/pages/professor/examens-prof.php';
    }
}
