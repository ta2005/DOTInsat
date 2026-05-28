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
        
        $config = require BASE_PATH . '/config/etudiant.php';

        // nhot nav active fll reclamation
        foreach ($config['nav'] as &$item) {
            $item['active'] = ($item['href'] === '/?page=reclamation');
        }

    //ba3d ma nesta3mel variable nfasakhha
        unset($item);

        $matieres  = $this->repo->getMatieres();
        // ll koll matiere n7otou les types d'evaluation mta3ha
        foreach ($matieres as &$m) {
            $m['types'] = $this->repo->getTypesByMatiere(
                (int) $m['id'],
                (int) $_SESSION['user_id']
            );
        }
        unset($m);

        require BASE_PATH . '/views/pages/student/reclamation.php';
    }
// tekhdem wakt ll formulaire yebda en POST 
public function store(): void
{
    $data = [
        'message'     => trim($_POST['commentaire'] ?? ''),
        'controle_id' => (int)($_POST['controle_id'] ?? 0), // ← corriger le nom
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


public function getMatieres(): void
{
    header('Content-Type: application/json');

    $etudiantId = (int)($_GET['num_inscription'] ?? 0);

    if ($etudiantId <= 0) {
        echo json_encode([]);
        exit;
    }

    $matieres = $this->repo->getMatieresParEtudiant($etudiantId);
    echo json_encode($matieres);
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