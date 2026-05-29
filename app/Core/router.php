<?php
declare(strict_types=1);



$routes = [

    [
        'page'       => 'home',
        'controller' => 'DispatcherController',
        'method'     => 'index',
        'http'       => 'GET',
        'roles'      => [
            ROLE_ETUDIANT,
            ROLE_PROFESSEUR,
            ROLE_ADMIN
        ]
    ],

   [
        'page'       => 'login',
        'controller' => 'AuthController',
        'method'     => 'showLogin',
        'http'       => 'GET',
        'roles'      => []
    ],

    [
        'page'       => 'do-login',
        'controller' => 'AuthController',
        'method'     => 'login',
        'http'       => 'POST',
        'roles'      => []
    ],

    [
        'page'       => 'logout',
        'controller' => 'AuthController',
        'method'     => 'logout',
        'http'       => 'ANY',
        'roles'      => []
    ],


    [
        'page'       => 'demande',
        'controller' => 'EtudiantController',
        'method'     => 'demandeIndex',
        'http'       => 'GET',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'save-demande',
        'controller' => 'EtudiantController',
        'method'     => 'demandeStore',
        'http'       => 'POST',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'delete-demande',
        'controller' => 'EtudiantController',
        'method'     => 'demandeDelete',
        'http'       => 'POST',
        'roles'      => [ROLE_ETUDIANT]
    ],

  
    [
        'page'       => 'reclamation',
        'controller' => 'EtudiantController',
        'method'     => 'reclamationIndex',
        'http'       => 'GET',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'save-reclamation',
        'controller' => 'EtudiantController',
        'method'     => 'reclamationStore',
        'http'       => 'POST',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'delete-reclamation',
        'controller' => 'EtudiantController',
        'method'     => 'reclamationDelete',
        'http'       => 'POST',
        'roles'      => [ROLE_ETUDIANT]
    ],


    [
        'page'       => 'api-get-matieres',
        'controller' => 'EtudiantController',
        'method'     => 'reclamationGetMatieres',
        'http'       => 'GET',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'examens',
        'controller' => 'EtudiantController',
        'method'     => 'examens',
        'http'       => 'GET',
        'roles'      => [ROLE_ETUDIANT]
    ],


    [
        'page'       => 'calcul-moyenne',
        'controller' => 'NotesController',
        'method'     => 'calculMoyenne',
        'http'       => 'GET',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'mes-notes',
        'controller' => 'NotesController',
        'method'     => 'mesNotes',
        'http'       => 'GET',
        'roles'      => [ROLE_ETUDIANT]
    ],

    [
        'page'       => 'demandes',
        'controller' => 'AdminController',
        'method'     => 'demandes',
        'http'       => 'GET',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'reclamations',
        'controller' => 'AdminController',
        'method'     => 'reclamations',
        'http'       => 'GET',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'update-demande-status',
        'controller' => 'AdminController',
        'method'     => 'updateDemandeStatus',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'update-reclamation-status',
        'controller' => 'AdminController',
        'method'     => 'updateReclamationStatus',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'etu_manage',
        'controller' => 'AdminEtudiantController',
        'method'     => 'index',
        'http'       => 'GET',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'etu_manage_save',
        'controller' => 'AdminEtudiantController',
        'method'     => 'store',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'etu_manage_update',
        'controller' => 'AdminEtudiantController',
        'method'     => 'update',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'etu_manage_delete',
        'controller' => 'AdminEtudiantController',
        'method'     => 'destroy',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],

    [
        'page'       => 'ens_manage',
        'controller' => 'AdminEnseignantController',
        'method'     => 'index',
        'http'       => 'GET',
        'roles'      => [ROLE_ADMIN]
    ],
 
    [
        'page'       => 'ens_manage_save',
        'controller' => 'AdminEnseignantController',
        'method'     => 'store',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],
 
    [
        'page'       => 'ens_manage_update',
        'controller' => 'AdminEnseignantController',
        'method'     => 'update',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],
 
    [
        'page'       => 'ens_manage_delete',
        'controller' => 'AdminEnseignantController',
        'method'     => 'destroy',
        'http'       => 'POST',
        'roles'      => [ROLE_ADMIN]
    ],

 
    [
        'page'       => 'prof-reclamations',
        'controller' => 'ProfessorController',
        'method'     => 'reclamations',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'prof-reclamation-action',
        'controller' => 'ProfessorController',
        'method'     => 'reclamationAction',
        'http'       => 'POST',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'examens-prof',
        'controller' => 'ProfessorController',
        'method'     => 'examens',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'qcm-dashboard',
        'controller' => 'QcmController',
        'method'     => 'dashboard',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'exam-delete',
        'controller' => 'QcmController',
        'method'     => 'deleteExam',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'qcm-create',
        'controller' => 'QcmController',
        'method'     => 'create',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'qcm-scan',
        'controller' => 'QcmController',
        'method'     => 'scan',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

  
    [
        'page'       => 'api-save-template',
        'controller' => 'QcmController',
        'method'     => 'saveTemplate',
        'http'       => 'POST',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'api-process-scan',
        'controller' => 'QcmController',
        'method'     => 'processScan',
        'http'       => 'POST',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'api-get-template',
        'controller' => 'QcmController',
        'method'     => 'getTemplate',
        'http'       => 'GET',
        'roles'      => [ROLE_PROFESSEUR]
    ],

    [
        'page'       => 'api-create-exam',
        'controller' => 'QcmController',
        'method'     => 'createExam',
        'http'       => 'POST',
        'roles'      => [ROLE_PROFESSEUR],
    ],

    [
        'page'       => 'api-modify-exam',
        'controller' => 'QcmController',
        'method'     => 'modifyExam',
        'http'       => 'POST',
        'roles'      => [ROLE_PROFESSEUR],
    ],

    [
        'page'       => 'api-modify-student-grade',
        'controller' => 'QcmController',
        'method'     => 'modifyStudentGrade',
        'http'       => 'POST',
        'roles'      => [ROLE_PROFESSEUR],
    ],


    [
        'page'       => 'forum',
        'controller' => 'ForumController',
        'method'     => 'index',
        'http'       => 'GET',
        'roles'      => [
            ROLE_ETUDIANT,
            ROLE_PROFESSEUR,
            ROLE_ADMIN
        ]
    ],

    [
        'page'       => 'save-post',
        'controller' => 'ForumController',
        'method'     => 'store',
        'http'       => 'POST',
        'roles'      => [
            ROLE_ETUDIANT,
            ROLE_PROFESSEUR,
            ROLE_ADMIN
        ]
    ],
];

//hedhi guard ta3 authentication, tchecki ken l'utilisateur ma3andouch session w ma3andouch cookies sahin, yredirectioni l login
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

    if (!$rawToken || !$cookieId) {
        header('Location: /?page=login');
        exit;
    }

    require_once BASE_PATH . '/repositories/AuthRepository.php';

    $authRepo = new AuthRepository($pdo);
    $user     = $authRepo->getUserByRememberToken($rawToken);

    if (!$user || (int)$user['id'] !== (int)$cookieId) {
        header('Location: /?page=login');
        exit;
    }

    session_regenerate_id(true);

    $_SESSION['user_id']     = $user['id'];
    $_SESSION['user_email']  = $user['email'];
    $_SESSION['user_nom']    = $user['nom']    ?? '';
    $_SESSION['user_prenom'] = $user['prenom'] ?? '';
    $_SESSION['user_role']   = $user['role']   ?? '';
    $_SESSION['filiere']     = $user['filiere'] ?? '';
    $_SESSION['annee']       = $user['annee']   ?? '';

    $newToken = $authRepo->createRememberToken((int)$user['id']);

    setcookie(COOKIE_REMEMBER_TOKEN, $newToken, [
        'expires'  => time() + REMEMBER_ME_DURATION,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// hedhi guard ta3 authorization, tchecki ken l'utilisateur 3andou role shah bach ykhdem l page
function role_guard(array $route): void
{
    if (empty($route['roles'])) {
        return;
    }

    $userRole = $_SESSION['user_role'] ?? '';

    if (!in_array($userRole, $route['roles'], true)) {
        renderError(403);
    }
}

// hedhi hiya fonction ta3 dispatch, tchecki l url w tmatch

function dispatch(array $routes, ?PDO $pdo): void
{
    auth_guard($pdo);

    $page       = $_GET['page'] ?? 'home';
    $httpMethod = $_SERVER['REQUEST_METHOD'];

    foreach ($routes as $route) {

        if ($route['page'] !== $page) {
            continue;
        }

        if (
            $route['http'] !== 'ANY'
            && $route['http'] !== $httpMethod
        ) {
            renderError(405);
        }

        role_guard($route);

        $controllerFile =
            BASE_PATH
            . '/app/Controllers/'
            . $route['controller']
            . '.php';

        if (!file_exists($controllerFile)) {
            renderError(500);
        }

        require_once $controllerFile;

        $controller = $route['controller'];
        $method     = $route['method'];

        if (
            !class_exists($controller)
            || !method_exists($controller, $method)
        ) {
            renderError(500);
        }

        $instance = new $controller($pdo);
        $instance->$method();

        return;
    }

    renderError(404);
}



function renderError(int $code): void
{
    http_response_code($code);

    echo match ($code) {
        403     => '403 - Accès interdit',
        404     => '404 - Page introuvable',
        405     => '405 - Méthode non autorisée',
        default => '500 - Erreur serveur',
    };

    exit;
}