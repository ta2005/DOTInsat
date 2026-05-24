<?php
try {
    $db_cnx = new PDO("mysql:host=localhost;dbname=dotinsat","root","");
}
catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}
