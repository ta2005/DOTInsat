<?php
declare(strict_types=1);


$routes = [

    // ── PAGES GÉNÉRALES ──────────────────────────────────────────────────────
    ['page' => 'home', 'controller' => 'HomeController', 'method' => 'index', 'http' => 'GET',
     'roles' => [ROLE_ETUDIANT, ROLE_PROFESSEUR, ROLE_ADMIN]],

    // ── AUTH (publiques) ─────────────────────────────────────────────────────
    ['page' => 'login',    'controller' => 'AuthController', 'method' => 'showLogin', 'http' => 'GET',  'roles' => []],
    ['page' => 'do-login', 'controller' => 'AuthController', 'method' => 'login',     'http' => 'POST', 'roles' => []],
    ['page' => 'logout',   'controller' => 'AuthController', 'method' => 'logout',    'http' => 'ANY',  'roles' => []],

    // ── DEMANDES (étudiant) ──────────────────────────────────────────────────
    ['page' => 'demande',        'controller' => 'DemandeController', 'method' => 'index',  'http' => 'GET',
     'roles' => [ROLE_ETUDIANT]],
    ['page' => 'save-demande',   'controller' => 'DemandeController', 'method' => 'store',  'http' => 'POST',
     'roles' => [ROLE_ETUDIANT]],
    ['page' => 'delete-demande', 'controller' => 'DemandeController', 'method' => 'delete', 'http' => 'POST',
     'roles' => [ROLE_ETUDIANT]],

    // ── RÉCLAMATIONS (étudiant) ──────────────────────────────────────────────
    ['page' => 'reclamation',        'controller' => 'ReclamationController', 'method' => 'index',  'http' => 'GET',
     'roles' => [ROLE_ETUDIANT]],
    ['page' => 'save-reclamation',   'controller' => 'ReclamationController', 'method' => 'store',  'http' => 'POST',
     'roles' => [ROLE_ETUDIANT]],
    ['page' => 'delete-reclamation', 'controller' => 'ReclamationController', 'method' => 'delete', 'http' => 'POST',
     'roles' => [ROLE_ETUDIANT]],

    // ── ADMIN ────────────────────────────────────────────────────────────────
    ['page' => 'demandes',                  'controller' => 'AdminController', 'method' => 'demandes',               'http' => 'GET',
     'roles' => [ROLE_ADMIN]],
    ['page' => 'reclamations',              'controller' => 'AdminController', 'method' => 'reclamations',           'http' => 'GET',
     'roles' => [ROLE_ADMIN]],
    ['page' => 'update-demande-status',     'controller' => 'AdminController', 'method' => 'updateDemandeStatus',    'http' => 'POST',
     'roles' => [ROLE_ADMIN]],
    ['page' => 'update-reclamation-status', 'controller' => 'AdminController', 'method' => 'updateReclamationStatus','http' => 'POST',
     'roles' => [ROLE_ADMIN]],

    // ── PROFESSEUR ───────────────────────────────────────────────────────────
    ['page' => 'prof-reclamations', 'controller' => 'ProfessorController', 'method' => 'reclamations', 'http' => 'GET',
     'roles' => [ROLE_PROFESSEUR]],
    ['page' => 'qcm-create',        'controller' => 'QcmController',       'method' => 'create',       'http' => 'GET',
     'roles' => [ROLE_PROFESSEUR]],
    ['page' => 'qcm-scan',          'controller' => 'QcmController',       'method' => 'scan',         'http' => 'GET',
     'roles' => [ROLE_PROFESSEUR]],

    // ── QCM API JSON ─────────────────────────────────────────────────────────
    ['page' => 'api-save-template', 'controller' => 'QcmController', 'method' => 'saveTemplate', 'http' => 'POST',
     'roles' => [ROLE_PROFESSEUR]],
    ['page' => 'api-process-scan',  'controller' => 'QcmController', 'method' => 'processScan',  'http' => 'POST',
     'roles' => [ROLE_PROFESSEUR]],
    ['page' => 'api-get-template',  'controller' => 'QcmController', 'method' => 'getTemplate',  'http' => 'GET',
     'roles' => [ROLE_PROFESSEUR]],

    // ── FORUM ────────────────────────────────────────────────────────────────
    ['page' => 'forum',     'controller' => 'ForumController', 'method' => 'index', 'http' => 'GET',
     'roles' => [ROLE_ETUDIANT, ROLE_PROFESSEUR, ROLE_ADMIN]],
    ['page' => 'save-post', 'controller' => 'ForumController', 'method' => 'store', 'http' => 'POST',
     'roles' => [ROLE_ETUDIANT, ROLE_PROFESSEUR, ROLE_ADMIN]],
];


function auth_guard(?PDO $pdo): void
{
    $page = $_GET['page'] ?? 'home';

    if (in_array($page, PUBLIC_PAGES, true)) {
        return;
    }

    if (!empty($_SESSION['user_id'])) {
        return;
    }

    $rawToken = $_COOKIE[COOKIE_REMEMBER_TOKEN] ?? null;
    $cookieId = $_COOKIE[COOKIE_REMEMBER_USER]  ?? null;

    if ($rawToken && $cookieId) {
        require_once BASE_PATH . '/app/Repositories/AuthRepository.php';
        $authRepo = new AuthRepository($pdo);
        $user     = $authRepo->getUserByRememberToken($rawToken);

        if ($user && (int)$user['id'] === (int)$cookieId) {
            session_regenerate_id(true);
            $_SESSION['user_id']     = $user['id'];
            $_SESSION['user_email']  = $user['email'];
            $_SESSION['user_nom']    = $user['nom']    ?? '';
            $_SESSION['user_prenom'] = $user['prenom'] ?? '';
            $_SESSION['user_role']   = $user['role']   ?? 'inconnu';

            $newToken = $authRepo->createRememberToken((int)$user['id']);
            setcookie(COOKIE_REMEMBER_TOKEN, $newToken, [
                'expires'  => time() + REMEMBER_ME_DURATION,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            return;
        }
    }

    header('Location: /?page=login');
    exit;
}


function role_guard(array $route): void
{
    if (empty($route['roles'])) {
        return;
    }

    $userRole = $_SESSION['user_role'] ?? '';

    if (!in_array($userRole, $route['roles'], true)) {
        renderError(null, 403);
        exit;
    }
}


function dispatch(array $routes, ?PDO $pdo): void
{
    auth_guard($pdo);

    $page       = $_GET['page'] ?? 'home';
    $httpMethod = $_SERVER['REQUEST_METHOD'];

    foreach ($routes as $route) {

        if ($route['page'] !== $page) {
            continue;
        }

        if ($route['http'] !== 'ANY' && $route['http'] !== $httpMethod) {
            renderError($pdo, 405);
            return;
        }

        role_guard($route);

        $controllerFile = BASE_PATH . '/app/Controllers/' . $route['controller'] . '.php';

        if (!file_exists($controllerFile)) {
            renderError($pdo, 500);
            return;
        }

        require_once $controllerFile;

        $class  = $route['controller'];
        $action = $route['method'];

        if (!class_exists($class) || !method_exists($class, $action)) {
            renderError($pdo, 500);
            return;
        }

        (new $class($pdo))->$action();
        return;
    }

    renderError($pdo, 404);
}


function renderError(?PDO $pdo, int $code): void
{
    http_response_code($code);
    exit;
}