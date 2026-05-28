<?php
// app/Controllers/AdminController.php

require_once BASE_PATH . '/app/Repositories/DemandeRepository.php';
require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';

class AdminController
{
    private DemandeRepository     $demandeRepo;
    private ReclamationRepository $reclamationRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->demandeRepo     = new DemandeRepository($this->pdo);
        $this->reclamationRepo = new ReclamationRepository($this->pdo);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=home (admin)
    |--------------------------------------------------------------------------
    */
    public function home(): void
    {
        $pdo    = $this->pdo;
        $config = require BASE_PATH . '/config/administrateur.php';
        require BASE_PATH . '/views/pages/home.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=demandes
    |--------------------------------------------------------------------------
    */
    public function demandes(): void
    {
        $pdo      = $this->pdo;
        $config   = require BASE_PATH . '/config/administrateur.php';
        $demandes = $this->demandeRepo->getAll();

        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/pages/admin/demandes.php';
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=reclamations
    |--------------------------------------------------------------------------
    */
    public function reclamations(): void
    {
        $pdo          = $this->pdo;
        $config       = require BASE_PATH . '/config/administrateur.php';
        $reclamations = $this->reclamationRepo->getAll();

        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/pages/admin/reclamations.php';
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=update-demande-status
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | POST ?page=update-reclamation-status
    |--------------------------------------------------------------------------
    */
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
