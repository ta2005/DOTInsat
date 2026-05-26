<?php

require_once BASE_PATH . '/app/Repositories/DemandeRepository.php';

class DemandeController
{
    private DemandeRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new DemandeRepository($this->pdo);
    }

    public function index(): void
    {
        $config = require BASE_PATH . '/config/etudiant.php';
        foreach ($config['nav'] as &$item) {
            $item['active'] = ($item['href'] === '/?page=demande');
        }
        unset($item);

        $demandes = $this->repo->getAll();

        // Types récupérés dynamiquement depuis l'ENUM BD
        $typesDemande = $this->repo->getTypesDemande();

        require BASE_PATH . '/views/pages/student/demande.php';
    }

    public function store(): void
    {
        // Récupérer les valeurs valides depuis la BD
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

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->repo->delete($id);
        header('Location: /?page=demande');
        exit;
    }
}