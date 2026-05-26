<?php
declare(strict_types=1);

session_start(); 

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/constants.php';
require_once BASE_PATH . '/config/db_connect.php';
require_once BASE_PATH . '/app/Core/router.php';
dispatch($routes, $pdo);
