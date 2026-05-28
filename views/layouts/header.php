<?php
//ll header navbar w les styles mtaa lpage
$currentPage = $_GET['page'] ?? 'home';
?>
<header>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">
    <nav class="topbar">
        <a href="/?page=home" class="brand">
            <img src="/resources/logo.svg" alt=".INSAT" class="brand-logo">
        </a>
        <ul class="nav">
            <?php
            if (!empty($config['nav']) && is_array($config['nav'])):
                foreach ($config['nav'] as $item):
                    // nekhou le href mtaa litem w nparseiha bach nverifi ken fih query string w nverifyi ken fih page parameter bach n3raf ken hadha houwa litem actif wala la
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
            <!-- Utilisateur connecté : afficher bouton déconnexion -->
            <div class="header-user">
                <a href="/?page=logout" class="connect-btn connect-btn--logout"
                    title="Se déconnecter">
                    <i class="ti ti-logout" aria-hidden="true"></i> Déconnexion
                </a>
            </div>
        <?php else: ?>
                <!-- Utilisateur non connecté : afficher bouton connexion fil page login-->
            <a href="/?page=login" class="connect-btn">Connexion</a>
        <?php endif; ?>
    </nav>
    <div class="brush-divider"></div>
</header>