<?php
declare(strict_types=1);

// -------------------------------------------------------------------------
// Bootstrap: autoload all app classes
// -------------------------------------------------------------------------
require_once __DIR__ . '/../config/db_connect.php';

// Interfaces (load first, concrete classes depend on them)
require_once __DIR__ . '/../app/Interfaces/IRepo.php';
require_once __DIR__ . '/../app/Interfaces/IControleRepository.php';

// Entities
require_once __DIR__ . '/../app/Entity/User.php';
require_once __DIR__ . '/../app/Entity/Group.php';
require_once __DIR__ . '/../app/Entity/Post.php';
require_once __DIR__ . '/../app/Entity/Controle.php';

// Base repository
require_once __DIR__ . '/../app/Repositories/Repo.php';

// Concrete repositories
require_once __DIR__ . '/../app/Repositories/UserRepo.php';
require_once __DIR__ . '/../app/Repositories/GroupRepo.php';
require_once __DIR__ . '/../app/Repositories/PostRepo.php';
require_once __DIR__ . '/../app/Repositories/ControleRepo.php';

// Services
require_once __DIR__ . '/../app/Services/QcmGradingService.php';

// Controllers
require_once __DIR__ . '/../app/Controllers/QcmController.php';

// -------------------------------------------------------------------------
// Routing
// -------------------------------------------------------------------------
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');

// ----- QCM API endpoints (JSON, no view) ---------------------------------
if ($path === '/api/qcm/save-template') {
    header('Content-Type: application/json; charset=utf-8');
    (new QcmController($pdo))->saveTemplate();
    exit;
}

if ($path === '/api/qcm/get-template') {
    header('Content-Type: application/json; charset=utf-8');
    (new QcmController($pdo))->getTemplate();
    exit;
}

if ($path === '/api/qcm/process-scan') {
    header('Content-Type: application/json; charset=utf-8');
    (new QcmController($pdo))->processScan();
    exit;
}

// ----- HTML page routes --------------------------------------------------
if ($path === '' || $path === '/index.php') {
    include __DIR__ . '/../views/pages/home.php';

} elseif ($path === '/qcm-create' || $path === '/professor/qcm-create') {
    include __DIR__ . '/../views/pages/professor/qcm-create.php';

} elseif ($path === '/qcm-scan' || $path === '/professor/qcm-scan') {
    include __DIR__ . '/../views/pages/professor/qcm-scan.php';

} elseif ($path === '/student/home') {
    include __DIR__ . '/../views/pages/student/Home.php';

} elseif ($path === '/login' || $path === '/auth/login') {
    include __DIR__ . '/../views/auth/login.php';

} elseif (file_exists(__DIR__ . $path)) {
    // Serve static files (CSS, JS, images, etc.) directly
    return false;

} else {
    http_response_code(404);
    // Graceful 404 fallback
    echo '<h1>404 – Page not found</h1>';
}
