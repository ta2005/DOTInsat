<?php
require_once BASE_PATH . '/app/Repositories/EtudiantRepository.php';
require_once BASE_PATH . '/app/Repositories/DemandeRepository.php';
require_once BASE_PATH . '/app/Repositories/ReclamationRepository.php';

class EtudiantController
{
    private EtudiantRepository    $etudiantRepo;
    private DemandeRepository     $demandeRepo;
    private ReclamationRepository $reclamationRepo;

    public function __construct(private PDO $pdo)
    {
        $this->etudiantRepo    = new EtudiantRepository($this->pdo);
        $this->demandeRepo     = new DemandeRepository($this->pdo);
        $this->reclamationRepo = new ReclamationRepository($this->pdo);
    }



    public function home(): void
    {
        $etudiantRepo = $this->etudiantRepo;
        $config       = require BASE_PATH . '/config/etudiant.php';
        require BASE_PATH . '/views/pages/home.php';
    }

    public function examens(): void
    {
        $etudiantRepo = $this->etudiantRepo;
        $config       = require BASE_PATH . '/config/etudiant.php';
        require BASE_PATH . '/views/pages/student/examens/index.php';
    }



    public function reclamationIndex(): void
    {
        $etudiantRepo    = $this->etudiantRepo;
        $reclamationRepo = $this->reclamationRepo;
        $config          = require BASE_PATH . '/config/etudiant.php';



        $matieres = $this->reclamationRepo->getMatieres();
        foreach ($matieres as &$m) {
            $m['types'] = $this->reclamationRepo->getTypesByMatiere(
                (int)$m['id'],
                (int)($_SESSION['user_id'] ?? 0)
            );
        }
        unset($m);

        $mesReclamations = $this->reclamationRepo->getMesReclamations(
            (int)($_SESSION['user_id'] ?? 0)
        );

        require BASE_PATH . '/views/pages/student/reclamation.php';
    }

    //bch yest3mlha fll form mtaa reclamation
    public function reclamationStore(): void
    {
        $data = [
            'message'     => trim($_POST['commentaire'] ?? ''),
            'controle_id' => (int)($_POST['controle_id'] ?? 0),
            'etudiant_id' => (int)($_SESSION['user_id']  ?? 0),
        ];

        if ($data['message'] && $data['controle_id'] && $data['etudiant_id']) {
            $this->reclamationRepo->create($data);
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Réclamation envoyée avec succès.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error',   'msg' => 'Tous les champs sont obligatoires.'];
        }

        header('Location: /?page=reclamation');
        exit;
    }

    // bch yest3mlha fll ajax request mtaa reclamation w bch yjib les matieres li 3andha relation b reclamation w les notes mta3hom
    // ajax howa nekhou donner men ghir ma nreloadiw page kamla

    public function reclamationGetMatieres(): void
    {
        header('Content-Type: application/json');

        $etudiantId = (int)($_GET['num_inscription'] ?? 0);
        if ($etudiantId <= 0) {
            echo json_encode([]);
            exit;
        }

        echo json_encode($this->reclamationRepo->getMatieresAvecNotes($etudiantId));
        exit;
    }


    public function reclamationDelete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->reclamationRepo->delete($id);

        header('Location: /?page=reclamation');
        exit;
    }


    
    public function demandeIndex(): void
    {
        $etudiantRepo = $this->etudiantRepo;
        $config       = require BASE_PATH . '/config/etudiant.php';

        $typesDemande = $this->demandeRepo->getTypesDemande();
        $mesDemandes  = $this->demandeRepo->getAllForEtudiant(
            (int)($_SESSION['user_id'] ?? 0)
        );

        require BASE_PATH . '/views/pages/student/demande.php';
    }


    public function demandeStore(): void
    {
        $typesValides = array_column($this->demandeRepo->getTypesDemande(), 'value');
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

        $this->demandeRepo->create([
            'message' => $message,
            'type'    => $type,
            'user_id' => $userId,
        ]);

        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Demande envoyée avec succès.'];
        header('Location: /?page=demande');
        exit;
    }

    
    public function demandeDelete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) $this->demandeRepo->delete($id);

        header('Location: /?page=demande');
        exit;
    }
}