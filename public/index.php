<?php
declare(strict_types=1);

// Get the requested path
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
$basePath = '';

// Handle requests
if ($path === '' || $path === '/index.php') {
    include '../views/pages/home.php';
} elseif ($path === '/qcm-create' || $path === '/professor/qcm-create') {
    include '../views/pages/professor/qcm-create.php';
} elseif ($path === '/qcm-scan' || $path === '/professor/qcm-scan') {
    include '../views/pages/professor/qcm-scan.php';
} elseif ($path === '/student/home') {
    include '../views/pages/student/Home.php';
} elseif ($path === '/login' || $path === '/auth/login') {
    include '../views/auth/login.php';
} elseif (file_exists('..' . $path)) {
    // Allow direct access to files (CSS, JS, images, etc.)
    return false;
} else {
    http_response_code(404);
    include '../views/pages/404.php'; // Create this if needed
}
?>
