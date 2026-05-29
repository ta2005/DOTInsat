<?php
// app/Controllers/AdminController.php

require_once BASE_PATH . '/app/Repositories/DemandeRepository.php';
require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';
require_once BASE_PATH . '/app/Repositories/AdminRepository.php';

class AdminController
{
    private DemandeRepository     $demandeRepo;
    private ReclamationRepository $reclamationRepo;
    private AdminRepository       $adminRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->demandeRepo     = new DemandeRepository($this->pdo);
        $this->reclamationRepo = new ReclamationRepository($this->pdo);
        $this->adminRepo       = new AdminRepository($this->pdo);
    }

    // aayetelha fll dispatcherController
    public function home(): void
    {
        $adminRepo = $this->adminRepo;
        // fll config mtaa ll admin fama des infos illi yest3mlhom fll home page
        $config    = require BASE_PATH . '/config/administrateur.php';
        require BASE_PATH . '/views/pages/home.php';
    }

 
    public function demandes(): void
    {
        $adminRepo = $this->adminRepo;
        $config    = require BASE_PATH . '/config/administrateur.php';
        $demandes  = $this->demandeRepo->getAll();

        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/pages/admin/demandes.php';
    }


    public function reclamations(): void
    {
        $adminRepo    = $this->adminRepo;
        $config       = require BASE_PATH . '/config/administrateur.php';
        $reclamations = $this->reclamationRepo->getAll();

        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/pages/admin/reclamations.php';
    }


    public function updateDemandeStatus(): void
    {
        $id     = (int)($_POST['id']     ?? 0);
        $statut = $_POST['statut']        ?? '';

        $allowed = ['ACCEPTEE', 'REFUSEE'];
        if ($id > 0 && in_array($statut, $allowed)) {
            $this->demandeRepo->updateStatut($id, $statut);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Statut mis à jour.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error',   'msg' => 'Données invalides.'];
        }

        header('Location: /?page=demandes');
        exit;
    }

    
    public function updateReclamationStatus(): void
    {
        $id     = (int)($_POST['id']     ?? 0);
        $statut = $_POST['statut']        ?? '';

        $allowed = ['ACCEPTEE_PAR_ADMINISTRATEUR', 'REFUSEE_PAR_ADMINISTRATEUR', 'EN_ATTENTE'];
        if ($id > 0 && in_array($statut, $allowed)) {
            $this->reclamationRepo->updateStatut($id, $statut);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Réclamation mise à jour.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error',   'msg' => 'Données invalides.'];
        }

        header('Location: /?page=reclamations');
        exit;
    }
}
