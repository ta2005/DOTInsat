<?php

require_once BASE_PATH . '/app/Repositories/PostRepo.php';
require_once BASE_PATH . '/app/Repositories/GroupRepo.php';

class ForumController
{
    private PostRepo  $postRepo;
    private GroupRepo $groupRepo;

    public function __construct(private ?PDO $pdo)
    {
        $this->postRepo  = new PostRepo($this->pdo);
        $this->groupRepo = new GroupRepo($this->pdo);
    }

    
    public function index(): void
    {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $userId = (int)($_SESSION['user_id'] ?? 0);
        $role   = $_SESSION['user_role'] ?? '';

        // Groupes pour sidebar illi inti fehom wala theb tzidhom
        $myGroups      = $this->groupRepo->fetchGroupsByUserId($userId);
        $unjoinedGroups = $this->groupRepo->fetchUnjoinedGroups($userId);

        // Filtre par groupe
        $filterGroupId = isset($_GET['group_id']) && $_GET['group_id'] !== ''
            ? (int)$_GET['group_id']
            : null;

        // Feed groupet illi inti fehom wala kolchi si ma fama filter
        if ($filterGroupId !== null) {
            $posts = $this->postRepo->fetchByGroup($filterGroupId);
        } else {
            $posts = $this->postRepo->fetchFeedByUserId($userId);
        }

        // Config specifique selon le rôle (pour la sidebar et autres éléments spécifiques)
        $config = $this->loadConfig($role);

        include BASE_PATH . '/views/pages/forum/forum.php';
    }

    // tsajel ll post illi taamalha jdida
    public function store(): void
    {
        $userId   = (int)($_SESSION['user_id'] ?? 0);
        $contenu  = trim($_POST['contenu']   ?? '');
        $groupeId = isset($_POST['groupe_id']) && $_POST['groupe_id'] !== ''
            ? (int)$_POST['groupe_id']
            : null;

        if ($contenu === '') {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Le contenu du post ne peut pas être vide.'];
            header('Location: /?page=forum');
            exit;
        }

        if ($groupeId === null) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Veuillez choisir un groupe.'];
            header('Location: /?page=forum');
            exit;
        }

        $result = $this->postRepo->create($contenu, $userId, $groupeId);

        if ($result !== null) {
            $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Post publié avec succès.'];
        } else {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Erreur lors de la publication.'];
        }

        header('Location: /?page=forum');
        exit;
    }

    // join group
    public function joinGroup(): void
    {
        $userId  = (int)($_SESSION['user_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);

        if ($groupId <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Groupe invalide.'];
            header('Location: /?page=forum');
            exit;
        }

        $ok = $this->groupRepo->joinGroup($userId, $groupId);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Vous avez rejoint le groupe.']
            : ['type' => 'error',   'msg' => 'Impossible de rejoindre ce groupe.'];

        header('Location: /?page=forum');
        exit;
    }

    // tokhrej mll groupe
    public function leaveGroup(): void
    {
        $userId  = (int)($_SESSION['user_id'] ?? 0);
        $groupId = (int)($_POST['group_id'] ?? 0);

        if ($groupId <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Groupe invalide.'];
            header('Location: /?page=forum');
            exit;
        }

        $ok = $this->groupRepo->leaveGroup($userId, $groupId);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Vous avez quitté le groupe.']
            : ['type' => 'error',   'msg' => 'Impossible de quitter ce groupe.'];

        header('Location: /?page=forum');
        exit;
    }

    // tfasakkh post
    public function deletePost(): void
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $postId = (int)($_POST['post_id'] ?? 0);

        if ($postId <= 0) {
            $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Post introuvable.'];
            header('Location: /?page=forum');
            exit;
        }

        $ok = $this->postRepo->delete($postId, $userId);
        $_SESSION['flash'] = $ok
            ? ['type' => 'success', 'msg' => 'Post supprimé.']
            : ['type' => 'error',   'msg' => 'Impossible de supprimer ce post.'];

        header('Location: /?page=forum');
        exit;
    }

    // tchargi ll config ll lezem 3la 7asb role si non etudiant par défaut
    private function loadConfig(string $role): array
    {
        return match ($role) {
            ROLE_ADMIN => (function () {
                require_once BASE_PATH . '/app/Repositories/AdminRepository.php';
                $adminRepo = new AdminRepository($this->pdo);
                return require BASE_PATH . '/config/administrateur.php';
            })(),
            ROLE_PROFESSEUR => (function () {
                require_once BASE_PATH . '/app/Repositories/ProfesseurRepository.php';
                require_once BASE_PATH . '/app/Repositories/EnseignementRepository.php';
                $profRepo = new ProfesseurRepository($this->pdo);
                $ensRepo  = new EnseignementRepository($this->pdo);
                return require BASE_PATH . '/config/enseignant.php';
            })(),
            default => (function () {
                require_once BASE_PATH . '/app/Repositories/EtudiantRepository.php';
                $etudiantRepo = new EtudiantRepository($this->pdo);
                return require BASE_PATH . '/config/etudiant.php';
            })(),
        };
    }
}
