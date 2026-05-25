<?php
// public/index.php
session_start();

// 1. IMPORT DB AND SRC FILES (going up one level to the root/src)
require_once '../db_connect.php'; 
require_once '../src/entity/User.php';
require_once '../src/entity/Group.php';
require_once '../src/entity/Post.php';
require_once '../src/repository/UserRepo.php';
require_once '../src/repository/GroupRepo.php';
require_once '../src/repository/PostRepo.php';

// 2. INITIALIZE REPOSITORIES (to be used by components)
$userRepo  = new UserRepo($pdo);
$groupRepo = new GroupRepo($pdo);
$postRepo  = new PostRepo($pdo);

// 3. ROLE & CONFIGURATION LOGIC
$role = $_SESSION['role'] ?? 'etudiant';
$rolesAutorises = ['etudiant', 'enseignant', 'administrateur'];
if (!in_array($role, $rolesAutorises)) {
    $role = 'enseignant';
}

// Load the configuration array
$config = require "config/{$role}.php";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Communauté — INSAT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/notifications.css">
    <link rel="stylesheet" href="css/blog.css">
    
</head>
<body>
<div class="wrap">

    <?php include 'components/header.php'; ?>

    <main>
        <?php include 'components/hero.php'; ?>
        
        <div class="blog-layout">
            <aside>
                <?php include 'components/actions.php'; ?>
            </aside>

            <section>
                <?php include 'components/feed.php'; ?>
            </section>
        </div>
    </main>

</div>
</body>
</html>
