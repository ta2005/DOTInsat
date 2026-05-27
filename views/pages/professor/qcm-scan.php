<?php
// views/pages/professor/qcm-scan.php

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

    <h2 class="page-title">Scanner QCM</h2>
    <p class="page-sub">Analysez automatiquement les feuilles de réponses étudiants.</p>

    <!-- filtres -->
    <div class="card" style="padding:18px 20px; margin-bottom:20px;">
        <div class="fields-row" style="margin-bottom:0;">
            <div class="field">
                <label for="scanExamId">ID de l'examen</label>
                <input type="number" id="scanExamId" min="1" value="14">
            </div>
            <div class="field">
                <label for="scanStudentId">ID étudiant (CIN / Matricule)</label>
                <input type="number" id="scanStudentId" min="1" value="1002">
            </div>
        </div>
    </div>

    <div class="scanner-grid">

        <!-- zone dépôt -->
        <div>
            <div id="dropzone" class="dropzone">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="#3b82f6"
                     viewBox="0 0 16 16" style="margin-bottom:12px; opacity:.7;">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                </svg>
                <h4>Glissez la feuille étudiant ici</h4>
                <p>Formats acceptés : PNG, JPG, JPEG</p>
                <input type="file" id="fileFallbackInput" accept="image/*" style="display:none;">
                <button type="button" class="btn btn-blue"
                        onclick="document.getElementById('fileFallbackInput').click()">
                    Parcourir
                </button>
            </div>
            <canvas id="processingCanvas"></canvas>
        </div>

        <!-- sidebar résultats -->
        <div class="card scan-sidebar">
            <h3>Console</h3>
            <hr>

            <div class="scan-field">
                <label>Étudiant</label>
                <div id="lblSessionStudent" class="val" style="color:#888;">En attente…</div>
            </div>

            <div class="scan-field">
                <label>Statut</label>
                <span id="pillStatus" class="pill">En attente</span>
            </div>

            <div class="scan-field">
                <label>Note calculée</label>
                <span id="lblCalculatedScore" class="score-val">0.00</span>
            </div>

            <label style="font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.06em; color:#555;">
                Journal
            </label>
            <div id="logStream" class="log-box">
                <div class="line">[Système] En attente d'une image…</div>
            </div>
        </div>

    </div>
</div>

<script src="/js/qcm-scanner.js"></script>
