<?php
// app/Controllers/AdminEtudiantController.php

require_once BASE_PATH . '/app/Repositories/EtudiantRepository.php';

class AdminEtudiantController
{
    private EtudiantRepository $repo;

    public function __construct(private ?PDO $pdo)
    {
        $this->repo = new EtudiantRepository($this->pdo);
    }

    /*
    |--------------------------------------------------------------------------
    | GET ?page=etudiants
    | Affiche la liste filtrée ou vide selon les paramètres GET
    |--------------------------------------------------------------------------
    */
    public function index(): void
    {
        $flash   = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $groupes  = $this->repo->getGroupes();
        $filieres = array_unique(array_column($groupes, 'filiere'));
        sort($filieres);

        // Grouper les classes par filière pour le JS
        $classesByFiliere = [];
        foreach ($groupes as $g) {
            $classesByFiliere[$g['filiere']][] = $g['classe'];
        }

        // Filtres courants (depuis GET)
        $filiere  = trim($_GET['filiere'] ?? '');
        $classe   = trim($_GET['classe']  ?? '');

        $etudiants = [];
        if ($filiere !== '' && $classe !== '') {
            $etudiants = $this->repo->getByFiliereEtClasse($filiere, $classe);
        }

        $pdo    = $this->pdo;
        $config = require BASE_PATH . '/config/administrateur.php';

        include BASE_PATH . '/views/layouts/header.php';
        include BASE_PATH . '/views/pages/admin/etudiants.php';
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=save-etudiant
    | Créer un nouvel étudiant
    |--------------------------------------------------------------------------
    */
    public function store(): void
    {
        $data = [
            'cin'      => $_POST['cin']      ?? null,
            'nom'      => trim($_POST['nom']      ?? ''),
            'prenom'   => trim($_POST['prenom']   ?? ''),
            'email'    => trim($_POST['email']    ?? ''),
            'mot_passe'=> $_POST['mot_passe']     ?? 'changeme123',
            'classe'   => trim($_POST['classe']   ?? ''),
            'filiere'  => trim($_POST['filiere']  ?? ''),
            'niveau'   => trim($_POST['niveau']   ?? ''),
            'annee'    => (int)($_POST['annee']   ?? date('Y')),
        ];

        if (
            $data['nom']     === '' ||
            $data['prenom']  === '' ||
            $data['email']   === '' ||
            $data['classe']  === '' ||
            $data['filiere'] === '' ||
            $data['niveau']  === ''
        ) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => 'Tous les champs obligatoires doivent être remplis.'
            ];
        } elseif ($this->repo->create($data)) {
            $_SESSION['flash'] = [
                'type' => 'success',
                'msg'  => 'Étudiant ajouté avec succès.'
            ];
        } else {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => 'Erreur lors de la création. Vérifiez l\'email ou le CIN.'
            ];
        }

        // Rediriger vers la même vue filtrée
        $filiere = $_POST['filiere'] ?? '';
        $classe  = $_POST['classe']  ?? '';
        header("Location: /?page=etu_manage&filiere={$filiere}&classe={$classe}");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=update-etudiant
    | Modifier un étudiant existant
    |--------------------------------------------------------------------------
    */
    public function update(): void
    {
        $id   = (int)($_POST['id'] ?? 0);
        $data = [
            'cin'     => $_POST['cin']     ?? null,
            'nom'     => trim($_POST['nom']     ?? ''),
            'prenom'  => trim($_POST['prenom']  ?? ''),
            'email'   => trim($_POST['email']   ?? ''),
            'classe'  => trim($_POST['classe']  ?? ''),
            'filiere' => trim($_POST['filiere'] ?? ''),
            'niveau'  => trim($_POST['niveau']  ?? ''),
            'annee'   => (int)($_POST['annee']  ?? date('Y')),
        ];

        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Étudiant introuvable.'];
        } elseif ($this->repo->update($id, $data)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Étudiant modifié avec succès.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Erreur lors de la modification.'];
        }

        $filiere = $_POST['filiere'] ?? '';
        $classe  = $_POST['classe']  ?? '';
        header("Location: /?page=etu_manage&filiere={$filiere}&classe={$classe}");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | POST ?page=delete-etudiant
    | Supprimer un étudiant
    |--------------------------------------------------------------------------
    */
    public function destroy(): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Étudiant introuvable.'];
        } elseif ($this->repo->delete($id)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Étudiant supprimé.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Impossible de supprimer cet étudiant.'];
        }

        $filiere = $_POST['filiere'] ?? '';
        $classe  = $_POST['classe']  ?? '';
        header("Location: /?page=etu_manage&filiere={$filiere}&classe={$classe}");
        exit;
    }
}
