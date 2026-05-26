<?php

$config = require BASE_PATH . '/config/etudiant.php';

require BASE_PATH . '/views/layouts/header.php';

?>

<div class="wrap">

    <div class="form-page-header">

        <div class="form-page-icon">
            <i class="ti ti-school"></i>
        </div>

        <div>

            <div class="form-page-title">
                Examens
            </div>

            <div class="form-page-sub">
                Gestion des examens et des moyennes
            </div>

        </div>

    </div>

    <div class="row row-actions">

        <!-- CALCUL MOYENNE -->
        <a href="/?page=calcul-moyenne"
           class="action-card">

            <i class="ti ti-calculator"></i>

            <span class="action-label">
                Calculer moyenne
            </span>

        </a>

        <!-- NOTES -->
        <a href="/?page=mes-notes"
           class="action-card">

            <i class="ti ti-report"></i>

            <span class="action-label">
                Voir mes notes
            </span>

        </a>

    </div>

</div>