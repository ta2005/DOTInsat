<?php
// page hedhi mtaa gestion prof

require_once BASE_PATH . '/app/Repositories/ProfesseurRepository.php';
require_once BASE_PATH . '/app/Repositories/AdminRepository.php';

class AdminEnseignantController
{
    private ProfesseurRepository $repo;
    private AdminRepository      $adminRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo      = new ProfesseurRepository($this->pdo);
        $this->adminRepo = new AdminRepository($this->pdo);
    }


    public function index(): void
    {
        $search = trim($_GET['q'] ?? '');
        $profs  = $this->repo->getAllWithEnseignements($search ?: null);
        $flash  = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $adminRepo = $this->adminRepo;
        $config    = require BASE_PATH . '/config/administrateur.php';

        //header w loun ll page 
        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/pages/admin/enseignants.php';
    }

  // ki bch najouti enseignant jdid
    public function store(): void
    {
        $data = [
            'cin'       => $_POST['cin']       ?? null,
            'nom'       => trim($_POST['nom']       ?? ''),
            'prenom'    => trim($_POST['prenom']    ?? ''),
            'email'     => trim($_POST['email']     ?? ''),
            'mot_passe' => $_POST['mot_passe']      ?? 'changeme123',
        ];

        if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Nom, prénom et email sont obligatoires.'];
            header('Location: /?page=ens_manage');
            exit;
        }

        $ok = $this->repo->create($data);

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Enseignant ajouté avec succès.']
            : ['type' => 'error',   'msg' => 'Erreur lors de l\'ajout (email déjà utilisé ?).'];
        // bch yrefreshi l page w ybadel flash message
        header('Location: /?page=ens_manage');
        exit;
    }

 
    public function update(): void
    {
        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            'cin'    => $_POST['cin']    ?? null,
            'nom'    => trim($_POST['nom']    ?? ''),
            'prenom' => trim($_POST['prenom'] ?? ''),
            'email'  => trim($_POST['email']  ?? ''),
        ];

        if ($id <= 0 || empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Données invalides.'];
            header('Location: /?page=ens_manage');
            exit;
        }

        $ok = $this->repo->update($id, $data);

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Enseignant modifié avec succès.']
            : ['type' => 'error',   'msg' => 'Erreur lors de la modification.'];

        header('Location: /?page=ens_manage');
        exit;
    }

   
    public function destroy(): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Enseignant introuvable.'];
            header('Location: /?page=ens_manage');
            exit;
        }

        $ok = $this->repo->delete($id);

        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Enseignant supprimé.']
            : ['type' => 'error',   'msg' => 'Erreur lors de la suppression.'];

        header('Location: /?page=ens_manage');
        exit;
    }
}
