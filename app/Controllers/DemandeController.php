<?php
// app/Controllers/DemandeController.php

require_once BASE_PATH . '/app/Repositories/DemandeRepository.php';
require_once BASE_PATH . '/app/Controllers/EtudiantController.php';

class DemandeController
{
    private DemandeRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new DemandeRepository($this->pdo);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=demande
    |--------------------------------------------------------------------------
    */
    public function index(): void
    {
        $etudiant = new EtudiantController($this->pdo);
        $config   = $etudiant->buildEtudiantConfig();

        foreach ($config['nav'] as &$item) {
            $item['active'] = ($item['href'] === '/?page=demande');
        }
        unset($item);

        $typesDemande = $this->repo->getTypesDemande();
        $mesDemandes  = $this->repo->getAllForEtudiant(
            (int)($_SESSION['user_id'] ?? 0)
        );

        require BASE_PATH . '/views/pages/student/demande.php';
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=save-demande
    |--------------------------------------------------------------------------
    */
    public function store(): void
    {
        $typesValides = array_column($this->repo->getTypesDemande(), 'value');
        $type         = $_POST['type'] ?? '';

        if (!in_array($type, $typesValides)) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Type de demande invalide.'];
            header('Location: /?page=demande');
            exit;
        }

        $autreText = trim($_POST['autre_type'] ?? '');
        $message   = trim($_POST['message']    ?? '');

        if ($type === 'AUTRE' && !$autreText) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Veuillez préciser votre demande.'];
            header('Location: /?page=demande');
            exit;
        }

        if ($type === 'AUTRE') {
            $message = '[' . $autreText . '] ' . $message;
        }

        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Vous devez être connecté.'];
            header('Location: /?page=login');
            exit;
        }

        $this->repo->create([
            'message' => $message,
            'type'    => $type,
            'user_id' => $userId,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Demande envoyée avec succès.'];
        header('Location: /?page=demande');
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=delete-demande
    |--------------------------------------------------------------------------
    */
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->repo->delete($id);
        header('Location: /?page=demande');
        exit;
    }
}
