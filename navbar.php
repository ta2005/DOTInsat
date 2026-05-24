<?php $current = basename($_SERVER['PHP_SELF']); ?>
<header>
    <img src="resources/logo.svg" alt=".INSAT" class="logo">
    <nav class="navbar">
        <ul class="navlinks">
            <li><a href="Home.php" class="<?= $current === 'Home.php' ? 'active' : '' ?>">Home</a></li>
            <li><a href="Blog.php" class="<?= $current === 'PFE.php' ? 'active' : '' ?>">Blog</a></li>
            <li><a href="Examens.php" class="<?= $current === 'PFE.php' ? 'active' : '' ?>">Examens</a></li>
            <li><a href="Reclamation.php" class="<?= $current === 'PFE.php' ? 'active' : '' ?>">Reclamation</a></li>
        </ul>
        </ul>
    </nav>
    <div class="header-right">
        <a href="login.php" class="connect-btn">Connecter</a>
    </div>
</header>
<img src="resources/brushLigne.png" alt="" class="header-line">