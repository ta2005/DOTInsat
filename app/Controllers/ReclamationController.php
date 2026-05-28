<?php
// app/Controllers/ReclamationController.php

require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';
require_once BASE_PATH . '/app/Controllers/EtudiantController.php';
require_once BASE_PATH . '/config/storage.php';

class ReclamationController
{
    private ReclamationRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new ReclamationRepository($this->pdo);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=reclamation
    |--------------------------------------------------------------------------
    */
    public function index(): void
    {
        $etudiant = new EtudiantController($this->pdo);
        $config   = $etudiant->buildEtudiantConfig();

        foreach ($config['nav'] as &$item) {
            $item['active'] = ($item['href'] === '/?page=reclamation');
        }
        unset($item);

        $matieres = $this->repo->getMatieres();

        foreach ($matieres as &$m) {
            $m['types'] = $this->repo->getTypesByMatiere(
                (int) $m['id'],
                (int) $_SESSION['user_id']
            );
        }
        unset($m);

        $matieres = array_values(array_filter($matieres, fn($m) => !empty($m['types'])));

        require BASE_PATH . '/views/pages/student/reclamation.php';
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=save-reclamation
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | POST ?page=delete-reclamation
    |--------------------------------------------------------------------------
    */
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->repo->delete($id);

        header('Location: /?page=reclamation');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=api-get-matieres
    |--------------------------------------------------------------------------
    */
    public function getMatieres(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $matieres = $this->repo->getMatieres();

        foreach ($matieres as &$m) {
            $m['types'] = $this->repo->getTypesByMatiere(
                (int) $m['id'],
                (int) $_SESSION['user_id']
            );
        }
        unset($m);

        echo json_encode($matieres);
        exit;
    }
}
