<?php


session_start();

// Rôle par défaut si pas de session
$role = $_SESSION['role'] ?? 'etudiant';

// Sécurité : n'autoriser que les rôles connus
$rolesAutorises = ['etudiant', 'enseignant', 'administrateur'];
if (!in_array($role, $rolesAutorises)) {
    $role = 'enseignant';
}

// Charger la config du rôle
$config = require "config/{$role}.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home — INSAT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="wrap">

    <?php include 'components/header.php'; ?>

    <main>
        <?php include 'components/hero.php'; ?>
        <?php include 'components/actions.php'; ?>
    </main>

</div>
</body>
</html>