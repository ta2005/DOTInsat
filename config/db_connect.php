<?php
$host = 'localhost';
$db   = 'blog_aymen';
$user = 'postgres';
$pass = ''; 

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
} catch (PDOException $e) {
    // Database connection failed, set $pdo to null to enable mock/testing fallback mode
    $pdo = null;
}
?>
