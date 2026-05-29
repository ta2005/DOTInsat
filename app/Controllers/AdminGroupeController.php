<?php

require_once BASE_PATH . '/app/Repositories/GroupRepo.php';
require_once BASE_PATH . '/app/Repositories/AdminRepository.php';

class AdminGroupeController
{
    private GroupRepo       $groupRepo;
    private AdminRepository $adminRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->groupRepo = new GroupRepo($this->pdo);
        $this->adminRepo = new AdminRepository($this->pdo);
    }

    public function index(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $allGroups = $this->groupRepo->fetchAllGroupNames();

        $adminRepo = $this->adminRepo;
        $config    = require BASE_PATH . '/config/administrateur.php';

        include BASE_PATH . '/views/pages/admin/groupes.php';
    }

    // creation groupe
    public function create(): void
    {
        $nom      = trim($_POST['new_group_name'] ?? '');
        $modEmail = trim($_POST['mod_email']       ?? '');

        if ($nom === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Le nom du groupe est obligatoire.'];
            header('Location: /?page=groupe_manage');
            exit;
        }

        if ($this->groupRepo->createGroupWithModEmail($nom, $modEmail ?: null)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "Le groupe '$nom' a été créé avec succès."];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Erreur lors de la création. Le nom existe peut-être déjà.'];
        }

        header('Location: /?page=groupe_manage');
        exit;
    }

    // tjid membrea groupe
    public function addMember(): void
    {
        $email     = trim($_POST['student_email'] ?? '');
        $groupName = trim($_POST['group_name']    ?? '');

        if ($email === '' || $groupName === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Email et groupe sont obligatoires.'];
            header('Location: /?page=groupe_manage');
            exit;
        }

        if ($this->groupRepo->addMemberByEmailAndGroupName($email, $groupName)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "L'utilisateur ($email) a été ajouté au groupe '$groupName'."];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => "Erreur : vérifiez l'email ou si l'utilisateur est déjà dans ce groupe."];
        }

        header('Location: /?page=groupe_manage');
        exit;
    }

    // suppression membre groupe
    public function removeMember(): void
    {
        $email     = trim($_POST['student_email'] ?? '');
        $groupName = trim($_POST['group_name']    ?? '');

        if ($email === '' || $groupName === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Email et groupe sont obligatoires.'];
            header('Location: /?page=groupe_manage');
            exit;
        }

        if ($this->groupRepo->removeMemberByEmailAndGroupName($email, $groupName)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => "L'utilisateur ($email) a été retiré du groupe '$groupName'."];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => "Erreur : vérifiez l'email ou si l'utilisateur est dans ce groupe."];
        }

        header('Location: /?page=groupe_manage');
        exit;
    }

    // tfasakh groupe
    public function destroy(): void
    {
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Groupe introuvable.'];
            header('Location: /?page=groupe_manage');
            exit;
        }

        if ($this->groupRepo->delete($id)) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Groupe supprimé.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Impossible de supprimer ce groupe.'];
        }

        header('Location: /?page=groupe_manage');
        exit;
    }
}
