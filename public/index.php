<?php
declare(strict_types=1);

// -------------------------------------------------------------------------
// 1. Bootstrap: Autoload all application dependencies
// -------------------------------------------------------------------------
require_once __DIR__ . '/../config/db_connect.php'; // Provides the active database $pdo instance

// Interfaces (Loaded first, concrete structures depend directly on these definitions)
require_once __DIR__ . '/../app/Interfaces/IRepo.php';
require_once __DIR__ . '/../app/Interfaces/IControleRepo.php';

// Entities / Data Models
require_once __DIR__ . '/../app/Entity/User.php';
require_once __DIR__ . '/../app/Entity/Group.php';
require_once __DIR__ . '/../app/Entity/Post.php';
require_once __DIR__ . '/../app/Entity/Controle.php';

// Base Abstract Repository Layer
require_once __DIR__ . '/../app/Repositories/Repo.php';

// Concrete Database Repositories
require_once __DIR__ . '/../app/Repositories/UserRepo.php';
require_once __DIR__ . '/../app/Repositories/GroupRepo.php';
require_once __DIR__ . '/../app/Repositories/PostRepo.php';
require_once __DIR__ . '/../app/Repositories/ControleRepo.php';

// Shared Repository Instantiation
$controleRepository = new \App\Repositories\ControleRepo($pdo);

// Analytical Core Services
require_once __DIR__ . '/../app/Services/QcmGradingService.php';

// Application Logic Controllers
require_once __DIR__ . '/../app/Controllers/QcmController.php';
require_once __DIR__ . '/../app/Controllers/ProfessorDashboardController.php';

// -------------------------------------------------------------------------
// 2. Request Parsing & Normalization Engine
// -------------------------------------------------------------------------
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

// -------------------------------------------------------------------------
// 3. API Background Communication Pipes (JSON Out, No HTML Views)
// -------------------------------------------------------------------------
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

// -------------------------------------------------------------------------
// 4. Form Action Execution Channels (POST Form Interceptors)
// -------------------------------------------------------------------------
if ($path === '/professor/exam/create' && $method === 'POST') {
    (new \App\Controllers\ProfessorDashboardController($pdo, $controleRepository))->handleCreateExam();
    exit;
}

// -------------------------------------------------------------------------
// 5. Interface Layout Presentation Layer (HTML Page Routes)
// -------------------------------------------------------------------------
if ($path === '' || $path === '/index.php') {
    include __DIR__ . '/../views/pages/home.php';

} elseif ($path === '/professor/dashboard') {
    // Dynamic entry point: executes tracking data lookup queries before serving layout view
    (new \App\Controllers\ProfessorDashboardController($pdo, $controleRepository))->index();

} elseif ($path === '/qcm-create' || $path === '/professor/qcm-create') {
    include __DIR__ . '/../views/pages/professor/qcm-create.php';

} elseif ($path === '/qcm-scan' || $path === '/professor/qcm-scan') {
    include __DIR__ . '/../views/pages/professor/qcm-scan.php';

} elseif ($path === '/student/home') {
    include __DIR__ . '/../views/pages/student/Home.php';

} elseif ($path === '/login' || $path === '/auth/login') {
    include __DIR__ . '/../views/auth/login.php';

} elseif (file_exists(__DIR__ . $path)) {
    // Direct passthrough pipeline for assets (CSS, JS, media layout graphics)
    return false;

} else {
    // Graceful routing exception fallback
    http_response_code(404);
    echo '<h1 style="font-family:sans-serif; text-align:center; margin-top:100px;">404 – Page not found</h1>';
}