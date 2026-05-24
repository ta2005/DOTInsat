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

<?php
    if (isset($_SESSION['user_id'])) {
        echo '<div class="header-right">';
        echo '<a href="inbox.php" class="inbox-btn">Notif.</a>';
        echo '</div>';
    } else {
        echo '<div class="header-right">';
        echo '<a href="login.php" class="connect-btn">Connecter</a>';
        echo '</div>';
    }
?>

</header>
<img src="resources/brushLigne.png" alt="" class="header-line">
