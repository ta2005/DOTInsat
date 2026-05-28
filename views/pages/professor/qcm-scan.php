<?php
// views/pages/professor/qcm-scan.php

$selectedExamId = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 14;
$selectedStudentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$selectedStudentCin = isset($_GET['student_cin']) ? (int) $_GET['student_cin'] : 1002;

$config = [
    'nav' => [
        ['href' => '/?page=home', 'label' => 'Accueil'],
        ['href' => '/?page=forum', 'label' => 'Blog'],
        ['href' => '/?page=examens-prof', 'label' => 'Examens'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];

require BASE_PATH . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-scanner.css">

<div class="calculator-page">
    <div class="container">
        <div class="calculator-wrapper">

            <div class="scanner-wrap">

                <h2>
                    Scanner les feuilles QCM
                </h2>

                <div class="scanner-filter">

                    <input type="hidden" id="scanRealStudentId"
                        value="<?= $selectedStudentId > 0 ? $selectedStudentId : '' ?>">
                    <input type="hidden" id="scanExamId" value="<?= $selectedExamId ?>">
                    <input type="hidden" id="scanStudentId" value="<?= $selectedStudentCin ?>">

                    <div class="input-wrapper">

                        <label for="scanExamIdSelect">
                            Choisir l'examen
                        </label>

                        <select id="scanExamIdSelect" class="form-select">
                            <option value="">Sélectionner un examen...</option>
                        </select>

                    </div>

                    <div class="input-wrapper">

                        <label for="scanStudentIdSelect">
                            Choisir l'étudiant
                        </label>

                        <select id="scanStudentIdSelect" class="form-select">
                            <option value="">Sélectionner un étudiant...</option>
                        </select>

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

                            <input type="file" id="fileFallbackInput" accept="image/*" style="display:none;">

                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('fileFallbackInput').click()">
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

                            <span id="pillStatus" class="status-badge status-en-attente">
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

                        <div class="result-block result-block--manual">

                            <label for="manualGradeInput">
                                Saisie Note Manuelle
                            </label>

                            <div class="manual-grade-row">
                                <input type="number" id="manualGradeInput" class="manual-grade-input" step="0.01"
                                    min="0" max="20" placeholder="Note / 20">
                                <button type="button" id="btnSubmitManualGrade" class="btn btn-primary btn-primary--sm">
                                    Valider
                                </button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
    // Embedded Exam & Student Datastore from Repository
    window.examData = <?= json_encode($exams, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    window.initialExamId = <?= (int) $selectedExamId ?>;
    window.initialStudentId = <?= (int) $selectedStudentId ?>;
    window.initialStudentCin = <?= (int) $selectedStudentCin ?>;
</script>
<script async src="https://docs.opencv.org/4.5.5/opencv.js" onload="onOpenCvReady();"
    onerror="onOpenCvError();"></script>
<script src="<?= BASE_URL ?>/js/qcm-scanner.js"></script>