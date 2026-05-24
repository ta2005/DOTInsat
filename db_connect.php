<?php
$host = 'localhost';
$db   = 'blog_aymen';
$user = 'postgres';
$pass = ''; 

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
