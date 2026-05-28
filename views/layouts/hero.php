<!-- components/hero.php — Hero + Profil (générique par rôle) -->
<head>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/notifications.css">
</head>
<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title">INSAT All In One Platform</h1>
        <p class="hero-subtitle">Jusqu'ici tout va bien</p>
        <div class="role-badge">
            <span class="role-dot"></span>
            <span>Connecté en tant que : <strong><?= htmlspecialchars($config['role']) ?></strong></span>
        </div>
    </div>

    <div class="hero-image-frame">
        <img src="resources/insat mel fou9.png" alt="Hero Image" class="hero-image">
    </div>
</section>

<!-- PROFILE CARD — Étudiant -->
<?php if ($config['role'] === 'Étudiant'): ?>

<div class="row row-full">
    <div class="card profile-card">
        <div class="profile-name"><?= htmlspecialchars($config['profile']['name']) ?></div>
        <div class="profile-row-bottom">
            <span class="profile-class"><?= htmlspecialchars($config['profile']['class']) ?></span>
            <span class="year-badge"><?= htmlspecialchars($config['profile']['year']) ?></span>
        </div>
    </div>
</div>

<!-- PROFILE CARD — Enseignant -->
<?php elseif ($config['role'] === 'Enseignant'):
    $activeClass = $config['profile']['selected_class'] ?? '—';
?>

<div class="row row-full">
    <div class="card profile-card prof-control-panel">

        <div class="prof-header-wrapper">
            <div class="prof-identity">
                <div class="profile-name"><?= htmlspecialchars($config['profile']['name']) ?></div>
                <div class="prof-year"><?= htmlspecialchars($config['profile']['year']) ?></div>
            </div>

            <form class="class-selector-form" method="GET" action="">
                <input type="hidden" name="page" value="home">
                <div class="select-wrapper">
                    <select id="class-select" name="selected_class" class="custom-select"
                            onchange="this.form.submit()">
                        <option value="" disabled>
                            Choisir une classe
                        </option>
                        <?php foreach ($config['profile']['classes'] as $class): ?>
                            <option value="<?= htmlspecialchars($class) ?>"
                                <?= ($activeClass === $class) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($class) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="current-showing-label">
            Statistiques actuelles :
            <span class="active-class"><?= htmlspecialchars($activeClass) ?></span>
        </div>

    </div>
</div>

<!-- PROFILE CARD — Administrateur -->
<?php elseif ($config['role'] === 'Administrateur'): ?>

<div class="row row-full">
    <div class="card profile-card admin-profile-card">
        <div class="admin-profile-left">
            <div class="admin-avatar">
                <i class="ti ti-shield-check" aria-hidden="true"></i>
            </div>
            <div class="admin-identity">
                <div class="profile-name"><?= htmlspecialchars($config['profile']['name']) ?></div>
                <div class="admin-title"><?= htmlspecialchars($config['profile']['title']) ?></div>
            </div>
        </div>
        <div class="year-badge"><?= htmlspecialchars($config['profile']['year']) ?></div>
    </div>
</div>

<?php endif; ?>