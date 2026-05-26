<?php

require_once BASE_PATH . '/app/Repositories/DemandeRepository.php';
require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';

class AdminController
{
    private DemandeRepository    $demandeRepo;
    private ReclamationRepository $reclamationRepo;

    // Le routeur passe $pdo au constructeur — obligatoire
    public function __construct(private ?PDO $pdo)
    {
        $this->demandeRepo     = new DemandeRepository($this->pdo);
        $this->reclamationRepo = new ReclamationRepository($this->pdo);
    }

    // GET ?page=admin-demandes
    public function demandes(): void
    {
        $demandes = $this->demandeRepo->getAll();
        require BASE_PATH . '/views/pages/admin/demandes.php';
    }

    // GET ?page=admin-reclamations
    public function reclamations(): void
    {
        $reclamations = $this->reclamationRepo->getAll();
        require BASE_PATH . '/views/pages/admin/reclamations.php';
    }

    // POST ?page=update-demande-status  (nom attendu par le routeur)
    public function updateDemandeStatus(): void
    {
        $id     = (int)($_POST['id']     ?? 0);
        $statut = $_POST['statut']        ?? '';

        $allowed = ['ACCEPTEE', 'REFUSEE'];
        if ($id > 0 && in_array($statut, $allowed)) {
            $this->demandeRepo->updateStatut($id, $statut);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Statut mis à jour.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Données invalides.'];
        }

        header('Location: ' . BASE_URL . '/?page=admin-demandes');
        exit;
    }

    // POST ?page=update-reclamation-status  (nom attendu par le routeur)
    public function updateReclamationStatus(): void
    {
        $id     = (int)($_POST['id']     ?? 0);
        $statut = $_POST['statut']        ?? '';

        $allowed = ['ACCEPTEE', 'REFUSEE', 'EN_ATTENTE'];
        if ($id > 0 && in_array($statut, $allowed)) {
            $this->reclamationRepo->updateStatut($id, $statut);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Réclamation mise à jour.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Données invalides.'];
        }

        header('Location: ' . BASE_URL . '/?page=admin-reclamations');
        exit;
    }
}
