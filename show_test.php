<?php
declare(strict_types=1);

require_once 'db_connect.php';
require_once 'src/entity/User.php';
require_once 'src/entity/Group.php';
require_once 'src/entity/Post.php';
require_once 'src/repository/UserRepo.php';
require_once 'src/repository/GroupRepo.php';
require_once 'src/repository/PostRepo.php';

$userRepo  = new UserRepo($pdo);
$groupRepo = new GroupRepo($pdo);
$postRepo  = new PostRepo($pdo);

// --- 1. FILL THE DATABASE (SEEDING) ---

// Check if we already have users to avoid duplicate seeding
$existingUsers = $userRepo->fetchAll();

if (count($existingUsers) < 2) {
    // Create Users
    $id1 = $userRepo->create(11111111, "Aymen", "Admin", "aymen@blog.com", password_hash("pass123", PASSWORD_DEFAULT));
    $id2 = $userRepo->create(22222222, "Sarah", "Writer", "sarah@blog.com", password_hash("pass123", PASSWORD_DEFAULT));
    $id3 = $userRepo->create(33333333, "John", "Explorer", "john@blog.com", password_hash("pass123", PASSWORD_DEFAULT));

    // Create Groups
    $groupTechId = $groupRepo->create("Tech Talk", $id1);
    $groupLifeId = $groupRepo->create("Life & Travel", $id2);

    // Create Posts
    $postRepo->create("Welcome to my new blog built with PHP and Postgres!", $id1, $groupTechId);
    $postRepo->create("I just discovered the Repository pattern, it's amazing!", $id2, $groupTechId);
    $postRepo->create("Does anyone have tips for traveling to Tunisia?", $id3, $groupLifeId);
    $postRepo->create("Postgres is much more powerful than I thought.", $id1, $groupTechId);

    echo "<div style='background:#d4edda; color:#155724; padding:10px; border-radius:5px;'>✅ Database seeded successfully!</div>";
}

// --- 2. FETCH ALL DATA TO SHOW ---
$allUsers  = $userRepo->fetchAll();
$allGroups = $groupRepo->fetchAll();
$allPosts  = $postRepo->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Blog Community Hub</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 40px; color: #333; background: #f4f7f6; }
        .container { max-width: 1000px; margin: auto; }
        .grid { display: grid; grid-template-columns: 1fr 2fr; gap: 20px; }
        section { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 10px; color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        .post-card { border-left: 4px solid #007bff; background: #f9fbff; padding: 10px; margin-bottom: 15px; }
        .meta { font-size: 0.85em; color: #777; }
    </style>
</head>
<body>

<div class="container">
    <h1>🚀 Blog Dashboard</h1>
    <hr>

    <div class="grid">
        <aside>
            <section>
                <h2>Active Users</h2>
                <table>
                    <thead><tr><th>Name</th><th>Email</th></tr></thead>
                    <tbody>
                        <?php foreach($allUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user->getNom()) ?></td>
                                <td><small><?= htmlspecialchars($user->getEmail()) ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section style="margin-top:20px;">
                <h2>Groups</h2>
                <ul>
                    <?php foreach($allGroups as $group): ?>
                        <li><strong><?= htmlspecialchars($group->getNom()) ?></strong></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        </aside>

        <main>
            <section>
                <h2>Recent Feed</h2>
                <?php foreach($allPosts as $post): ?>
                    <div class="post-card">
                        <div class="meta">
                            User #<?= $post->getIdUser() ?> | <?= $post->getDateDeCreation()->format('M d, Y H:i') ?>
                        </div>
                        <p><?= nl2br(htmlspecialchars($post->getContenu())) ?></p>
                    </div>
                <?php endforeach; ?>
            </section>
        </main>
    </div>
</div>

</body>
</html>
