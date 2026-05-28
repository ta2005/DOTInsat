<?php
declare(strict_types=1);

ini_set('display_errors', '1');
error_reporting(E_ALL);

// 1. Définir BASE_PATH dès le début (tout le reste en dépend)
define('BASE_PATH', str_replace('\\', '/', dirname(__DIR__)));

// 2. Constantes de l'application (rôles, cookies, durées…)
require_once BASE_PATH . '/config/constants.php';

// 3. Démarrer la session PHP AVANT toute lecture de $_SESSION ou setcookie()
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// 4. Connexion à la base de données
require_once BASE_PATH . '/config/db_connect.php';

// 5. Router → contient dispatch(), auth_guard(), role_guard()
require_once BASE_PATH . '/app/Core/router.php';

dispatch($routes, $pdo);