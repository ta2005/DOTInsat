<?php
// views/pages/professor/qcm-create.php

$examId = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : '';

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

<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-builder.css">

<div class="calculator-page no-print-layer">
<div class="container">
<div class="calculator-wrapper">

<div class="qcm-wrap">

    <h2>Créer une clé QCM</h2>

    <span class="text-muted">
        Définissez la structure du QCM et la matrice des réponses.
    </span>

    <div class="config-card">

        <div class="setup-row">

            <div class="input-wrapper">

                <label for="controleId">
                    ID examen
                </label>

                <input
                    type="number"
                    id="controleId"
                    min="1"
                    placeholder="Ex : 14"
                    value="<?= $examId ?>"
                    required
                >

            </div>

            <div class="input-wrapper">

                <label for="totalQuestions">
                    Nombre de questions
                </label>

                <input
                    type="number"
                    id="totalQuestions"
                    min="10"
                    max="40"
                    value="20"
                    required
                >

            </div>

            <div class="input-wrapper">

                <label for="choicesPerQuestion">
                    Choix par question
                </label>

                <input
                    type="number"
                    id="choicesPerQuestion"
                    min="2"
                    max="5"
                    value="4"
                    required
                >

            </div>

        </div>

        <button
            type="button"
            id="btnInitializeWorkspace"
            class="btn btn-primary"
        >
            Générer la matrice
        </button>

    </div>

    <form id="qcmMasterKeyForm" style="display:none;">

        <h3>
            Réponses correctes
        </h3>

        <span class="text-muted">
            Sélectionnez la bonne réponse pour chaque question.
        </span>

        <div id="interactiveMatrixWorkspace"></div>

        <div class="control-actions">

            <button
                type="submit"
                class="btn btn-primary"
            >
                Enregistrer
            </button>

            <button
                type="button"
                id="TriggerPrintJob"
                class="btn btn-secondary"
            >
                Imprimer
            </button>

        </div>

    </form>

</div>

</div>
</div>
</div>

<div id="printableBubbleDocument">

    <div class="doc-title-block">

        <h1>
            FEUILLE QCM
        </h1>

        <p>
            Remplissez les cercles correctement.
        </p>

    </div>

    <div class="student-info-grid">

        <div>

            <p>
                <strong>CIN :</strong>
                ___________________
            </p>

            <p>
                <strong>Nom :</strong>
                ___________________
            </p>

            <p>
                <strong>Prénom :</strong>
                ___________________
            </p>

        </div>

        <div>

            <p>
                <strong>Examen :</strong>

                <span id="lblDocumentExamRef">
                    #--
                </span>
            </p>

            <p>
                <strong>Classe :</strong>
                ___________________
            </p>

            <p>
                <strong>Date :</strong>
                ___________________
            </p>

        </div>

    </div>

    <div
        class="sheet-columns-wrapper"
        id="documentGridInversionTarget"
    ></div>

</div>

<script src="<?= BASE_URL ?>/js/qcm-builder.js"></script>