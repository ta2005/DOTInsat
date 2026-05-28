<?php
// app/Controllers/ReclamationController.php

require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';

class ReclamationController
{
    private ReclamationRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new ReclamationRepository($this->pdo);
    }

    // -------------------------------------------------------------------------
    // GET ?page=reclamation
    // -------------------------------------------------------------------------
    public function index(): void
    {
        $reclamationRepo = $this->repo;
        $config          = require BASE_PATH . '/config/reclamation.php';

        // Marquer l'entrée nav active
        foreach ($config['nav'] as &$item) {
            $item['active'] = ($item['href'] === '/?page=reclamation');
        }
        unset($item);

        $matieres = $this->repo->getMatieres();

        foreach ($matieres as &$m) {
            $m['types'] = $this->repo->getTypesByMatiere(
                (int)$m['id'],
                (int)($_SESSION['user_id'] ?? 0)
            );
        }
        unset($m);


        $mesReclamations = $this->repo->getMesReclamations(
            (int)($_SESSION['user_id'] ?? 0)
        );
        require BASE_PATH . '/views/pages/student/reclamation.php';
    }

    // -------------------------------------------------------------------------
    // POST ?page=save-reclamation
    // -------------------------------------------------------------------------
    public function store(): void
    {
        $data = [
            'message'     => trim($_POST['commentaire'] ?? ''),
            'controle_id' => (int)($_POST['controle_id'] ?? 0),
            'etudiant_id' => (int)($_SESSION['user_id']  ?? 0),
        ];

        if ($data['message'] && $data['controle_id'] && $data['etudiant_id']) {
            $this->repo->create($data);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Réclamation envoyée avec succès.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tous les champs sont obligatoires.'];
        }

        header('Location: /?page=reclamation');
        exit;
    }

    // -------------------------------------------------------------------------
    // GET ?page=matieres-json (API JSON)
    // -------------------------------------------------------------------------
    public function getMatieres(): void
    {
        header('Content-Type: application/json');

        $etudiantId = (int)($_GET['num_inscription'] ?? 0);

        if ($etudiantId <= 0) {
            echo json_encode([]);
            exit;
        }

        echo json_encode($this->repo->getMatieresAvecNotes($etudiantId));
        exit;
    }

    // -------------------------------------------------------------------------
    // POST ?page=delete-reclamation
    // -------------------------------------------------------------------------
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->repo->delete($id);

        header('Location: /?page=reclamation');
        exit;
    }
}
