<?php
// views/pages/professor/qcm-scan.php

$config = [
    'nav' => [
        ['href' => '/?page=home',              'label' => 'Accueil'],
        ['href' => '/?page=forum',             'label' => 'Blog'],
        ['href' => '/?page=examens-prof',      'label' => 'Examens'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];

require BASE_PATH . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof.css">

<div class="calculator-page">
<div class="container">
<div class="calculator-wrapper">

<div class="scanner-wrap">

    <h2>
        Scanner les feuilles QCM
    </h2>

    <div class="scanner-filter">

        <div class="input-wrapper">

            <label for="scanExamId">
                ID examen
            </label>

            <input
                type="number"
                id="scanExamId"
                min="1"
                value="14"
                required
            >

        </div>

        <div class="input-wrapper">

            <label for="scanStudentId">
                CIN étudiant
            </label>

            <input
                type="number"
                id="scanStudentId"
                min="1"
                value="1002"
                required
            >

        </div>

    </div>

    <div class="scanner-layout">

        <!-- LEFT -->

        <div>

            <div id="dropzone" class="dropzone-container">

                <h3>
                    Déposez la feuille ici
                </h3>

                <p>
                    PNG / JPG / JPEG
                </p>

                <input
                    type="file"
                    id="fileFallbackInput"
                    accept="image/*"
                    style="display:none;"
                >

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="document.getElementById('fileFallbackInput').click()"
                >
                    Parcourir
                </button>

            </div>

            <canvas id="processingCanvas"></canvas>

        </div>

        <!-- RIGHT -->

        <div class="results-sidebar">

            <h3>
                Résultats
            </h3>

            <hr>

            <div class="result-block">

                <label>
                    Étudiant
                </label>

                <div id="lblSessionStudent">
                    En attente...
                </div>

            </div>

            <div class="result-block">

                <label>
                    Statut
                </label>

                <span
                    id="pillStatus"
                    class="status-badge status-en-attente"
                >
                    En attente
                </span>

            </div>

            <div class="result-block">

                <label>
                    Note
                </label>

                <div id="lblCalculatedScore" class="score-badge">
                    0.00
                </div>

            </div>

        </div>

    </div>

</div>

</div>
</div>
</div>

<script src="<?= BASE_URL ?>/js/qcm-scanner.js"></script>