<?php

require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';
require_once BASE_PATH . '/config/storage.php';

class ReclamationController
{
    private ReclamationRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new ReclamationRepository($this->pdo);
    }

    public function index(): void
    {
        // Charger la config étudiant pour le header (nav, profil...)
        $config = require BASE_PATH . '/config/etudiant.php';

        // Marquer Réclamations comme actif dans la nav
        foreach ($config['nav'] as &$item) {
            $item['active'] = ($item['href'] === '/?page=reclamation');
        }
        unset($item);

        $matieres  = $this->repo->getMatieres();
        $typesEval = [
            ['value' => 'DS',   'label' => 'DS'],
            ['value' => 'EXAM', 'label' => 'Examen'],
            ['value' => 'TP',   'label' => 'TP'],
        ];

        require BASE_PATH . '/views/pages/student/reclamation.php';
    }

    public function store(): void
    {
        $data = [
            'message'         => trim($_POST['commentaire']      ?? ''),
            'type_controle'   => $_POST['type_eval']             ?? 'DS',
            'enseignement_id' => (int)($_POST['matiere']         ?? 0),
            'etudiant_id'     => (int)($_SESSION['user_id']      ?? 0),
        ];

        if ($data['message'] && $data['enseignement_id'] && $data['etudiant_id']) {
            $this->repo->create($data);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Réclamation envoyée avec succès.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tous les champs sont obligatoires.'];
        }

        header('Location: /?page=reclamation');
        exit;
    }

    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->repo->delete($id);

        header('Location: /?page=reclamation');
        exit;
    }
}