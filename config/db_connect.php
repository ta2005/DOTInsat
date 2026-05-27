<?php
<<<<<<< HEAD
function get_pdo(): ?PDO {
    static $instance = 'unset';
=======
$host = 'localhost';
$db = 'dotinsat';
$user = 'postgres';
$pass = 'root';
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c

    if ($instance !== 'unset') return $instance;

    $host = 'localhost';
    $port = '5432';
    $db   = 'dotinsat';
    $user = 'postgres';
    $pass = '123456';

    try {
        $instance = new PDO(
            "pgsql:host=$host;port=$port;dbname=$db",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
    die('Erreur BD : ' . $e->getMessage());
}
<<<<<<< HEAD

    return $instance;
}

$pdo = get_pdo();
=======
?>
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
