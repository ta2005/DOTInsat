<?php
declare(strict_types=1);

$routes = [

    
    //  PAGES GÉNÉRALES
   
    ['page' => 'home',       'controller' => 'HomeController',       'method' => 'index',    'http' => 'GET'],


    //  DEMANDES (étudiant)
  
    ['page' => 'demande',        'controller' => 'DemandeController', 'method' => 'index',  'http' => 'GET'],
    ['page' => 'save-demande',   'controller' => 'DemandeController', 'method' => 'store',  'http' => 'POST'],
    ['page' => 'delete-demande', 'controller' => 'DemandeController', 'method' => 'delete', 'http' => 'POST'],

    //  RÉCLAMATIONS (étudiant)

    ['page' => 'reclamation',        'controller' => 'ReclamationController', 'method' => 'index',  'http' => 'GET'],
    ['page' => 'save-reclamation',   'controller' => 'ReclamationController', 'method' => 'store',  'http' => 'POST'],
    ['page' => 'delete-reclamation', 'controller' => 'ReclamationController', 'method' => 'delete', 'http' => 'POST'],


    //  ADMIN

    ['page' => 'admin-demandes',             'controller' => 'AdminController', 'method' => 'demandes',               'http' => 'GET'],
    ['page' => 'admin-reclamations',         'controller' => 'AdminController', 'method' => 'reclamations',           'http' => 'GET'],
    ['page' => 'update-demande-status',      'controller' => 'AdminController', 'method' => 'updateDemandeStatus',    'http' => 'POST'],
    ['page' => 'update-reclamation-status',  'controller' => 'AdminController', 'method' => 'updateReclamationStatus','http' => 'POST'],


    //  PROFESSEUR

    ['page' => 'prof-reclamations', 'controller' => 'ProfessorController', 'method' => 'reclamations', 'http' => 'GET'],
    ['page' => 'qcm-create',        'controller' => 'QcmController',       'method' => 'create',       'http' => 'GET'],
    ['page' => 'qcm-scan',          'controller' => 'QcmController',       'method' => 'scan',         'http' => 'GET'],


    //  QCM API (JSON endpoints)

    ['page' => 'api-save-template', 'controller' => 'QcmController', 'method' => 'saveTemplate', 'http' => 'POST'],
    ['page' => 'api-process-scan',  'controller' => 'QcmController', 'method' => 'processScan',  'http' => 'POST'],
    ['page' => 'api-get-template',  'controller' => 'QcmController', 'method' => 'getTemplate',  'http' => 'GET'],

 
    //  AUTH

    ['page' => 'login',    'controller' => 'AuthController', 'method' => 'showLogin', 'http' => 'GET'],
    ['page' => 'do-login', 'controller' => 'AuthController', 'method' => 'login',     'http' => 'POST'],
    ['page' => 'logout',   'controller' => 'AuthController', 'method' => 'logout',    'http' => 'ANY'],

   
    //  FORUM

    ['page' => 'forum',     'controller' => 'ForumController', 'method' => 'index', 'http' => 'GET'],
    ['page' => 'save-post', 'controller' => 'ForumController', 'method' => 'store', 'http' => 'POST'],
];


//  DISPATCH


function dispatch(array $routes, ?PDO $pdo): void
{
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

    // Aucune route matchée → 404
    renderError($pdo, 404);
}


function renderError(?PDO $pdo, int $code): void
{
    http_response_code($code);

    require_once BASE_PATH . '/app/Controllers/ErrorController.php';

    $controller = new ErrorController($pdo);

    match ($code) {
        404     => $controller->notFound(),
        405     => $controller->methodNotAllowed(),
        default => $controller->serverError(),
    };
}