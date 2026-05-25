<?php
$host = 'localhost';
$db = 'dotinsat';
$user = 'postgres';
$pass = 'root';

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
} catch (PDOException $e) {
    // Database connection failed, set $pdo to null to enable mock/testing fallback mode
    $pdo = null;
}
?>