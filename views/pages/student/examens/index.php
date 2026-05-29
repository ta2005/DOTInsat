<?php

$config = require BASE_PATH . '/config/etudiant.php';

require BASE_PATH . '/views/layouts/header.php';

?>

<div class="wrap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <div class="form-page-header">
        <div class="form-page-icon">
            <i class="ti ti-school"></i>
        </div>
        <div>
            <div class="form-page-title">Examens</div>
            <div class="form-page-sub">Gestion des examens et des moyennes</div>
        </div>
    </div>

    <div class="row row-actions">
        
        <a href="/?page=calcul-moyenne" class="action-card">
            <i class="ti ti-calculator" aria-hidden="true"></i>
            <span class="action-label">Calculer moyenne</span>
        </a>

        <a href="/?page=mes-notes" class="action-card">
            <i class="ti ti-report" aria-hidden="true"></i>
            <span class="action-label">Voir mes notes</span>
        </a>

    </div>

</div>