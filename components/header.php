<?php
// components/header.php — Navbar dynamique
// IMPORTANT : $config doit être chargé dans index.php AVANT d'inclure ce fichier
?>
<header>
    <nav class="topbar">
        <a href="index.php" class="brand">
            <img src="resources/logo.svg" alt=".INSAT" class="brand-logo">
        </a>
        <ul class="nav">
            <?php
            // Sécurité : vérifier que nav existe dans $config
            if (!empty($config['nav']) && is_array($config['nav'])):
                foreach ($config['nav'] as $item):
            ?>
            <li>
                <a href="<?= htmlspecialchars($item['href'] ?? '#') ?>"
                   <?= !empty($item['active']) ? 'class="active"' : '' ?>>
                    <?= htmlspecialchars($item['label'] ?? '') ?>
                </a>
            </li>
            <?php
                endforeach;
            endif;
            ?>
        </ul>
        <a href="login.php" class="connect-btn">Connecter</a>
    </nav>
    <div class="brush-divider"></div>
</header>