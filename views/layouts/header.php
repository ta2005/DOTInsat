<?php
// views/layouts/header.php
// $config doit être défini par le controller avant d'inclure ce fichier
$currentPage = $_GET['page'] ?? 'home';
?>
<header>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/notifications.css">
    <nav class="topbar">
        <a href="/?page=home" class="brand">
            <img src="/resources/logo.svg" alt=".INSAT" class="brand-logo">
        </a>
        <ul class="nav">
            <?php
            if (!empty($config['nav']) && is_array($config['nav'])):
                foreach ($config['nav'] as $item):
                    // Extraire la valeur de ?page= depuis le href pour la comparaison active
                    $href = $item['href'] ?? '#';
                    parse_str(parse_url($href, PHP_URL_QUERY) ?? '', $qs);
                    $isActive = isset($qs['page']) && $qs['page'] === $currentPage;
            ?>
                    <li>
                        <a href="<?= htmlspecialchars($href) ?>"
                            <?= $isActive ? 'class="active"' : '' ?>>
                            <?= htmlspecialchars($item['label'] ?? '') ?>
                        </a>
                    </li>
            <?php
                endforeach;
            endif;
            ?>
        </ul>

        <?php if (!empty($_SESSION['user_id'])): ?>
            <!-- Utilisateur connecté : afficher nom + bouton déconnexion -->
            <div class="header-user">
                <a href="/?page=logout" class="connect-btn connect-btn--logout"
                    title="Se déconnecter">
                    <i class="ti ti-logout" aria-hidden="true"></i> Déconnexion
                </a>
            </div>
        <?php else: ?>
            <a href="/?page=login" class="connect-btn">Connexion</a>
        <?php endif; ?>
    </nav>
    <div class="brush-divider"></div>
</header>