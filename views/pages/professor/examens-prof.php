<?php
// views/pages/professor/examens-prof.php

$config = [
    'nav' => [
        ['href' => '/?page=home',             'label' => 'Accueil'],
        ['href' => '/?page=forum',             'label' => 'Blog'],
        ['href' => '/?page=examens-prof',      'label' => 'Examens'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];

require BASE_PATH . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="/css/qcm.css">

<div class="qcm-wrap">

    <h2 class="page-title">Examens</h2>
    <p class="page-sub">Choisissez une action</p>

    <div class="action-grid">

        <a href="/?page=qcm-dashboard" class="action-card">
            <i class="ti ti-layout-dashboard icon"></i>
            <div>
                <div class="label">Dashboard</div>
                <div class="desc">Voir tous les examens par cours</div>
            </div>
        </a>

        <a href="/?page=qcm-create" class="action-card">
            <i class="ti ti-file-plus icon"></i>
            <div>
                <div class="label">Créer QCM</div>
                <div class="desc">Construire la clé de correction</div>
            </div>
        </a>

        <a href="/?page=qcm-scan" class="action-card">
            <i class="ti ti-scan icon"></i>
            <div>
                <div class="label">Scanner QCM</div>
                <div class="desc">Analyser les feuilles étudiants</div>
            </div>
        </a>

    </div>

</div>
