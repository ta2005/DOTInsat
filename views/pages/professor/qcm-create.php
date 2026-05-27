<?php
// views/pages/professor/qcm-create.php

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

<div class="qcm-wrap no-print">

    <h2 class="page-title">Créer une clé de correction QCM</h2>
    <p class="page-sub">Configurez les paramètres puis assignez les bonnes réponses.</p>

    <!-- config -->
    <div class="card" style="padding:20px; margin-bottom:24px;">
        <div class="fields-row">
            <div class="field">
                <label for="controleId">ID de l'examen</label>
                <input type="number" id="controleId" min="1"
                       value="<?= (int)($_GET['exam_id'] ?? 0) ?: '' ?>"
                       placeholder="ex : 14">
            </div>
            <div class="field">
                <label for="totalQuestions">Questions (10–40)</label>
                <input type="number" id="totalQuestions" min="10" max="40" value="20">
            </div>
            <div class="field">
                <label for="choicesPerQuestion">Choix par question (2–5)</label>
                <input type="number" id="choicesPerQuestion" min="2" max="5" value="4">
            </div>
        </div>
        <button type="button" id="btnInitializeWorkspace" class="btn btn-blue">
            Générer la matrice
        </button>
    </div>

    <!-- matrice -->
    <form id="qcmMasterKeyForm" style="display:none;">
        <p style="font-size:.87rem; color:#888; margin-bottom:16px;">
            Sélectionnez la bonne réponse et le coefficient de chaque question.
        </p>

        <div id="interactiveMatrixWorkspace" class="matrix-grid"></div>

        <div class="sticky-bar">
            <button type="submit" class="btn btn-green">Enregistrer la clé maître</button>
            <button type="button" id="TriggerPrintJob" class="btn btn-gray">Imprimer la feuille vide</button>
            <a href="?page=qcm-dashboard" class="btn btn-gray">← Retour</a>
        </div>
    </form>

</div>

<!-- feuille imprimable -->
<div id="printDoc">
    <div class="print-title">
        <h1>FEUILLE DE RÉPONSES — QCM</h1>
        <p>Noircissez entièrement les cercles. Remplissez vos informations lisiblement.</p>
    </div>
    <div class="print-info">
        <div>
            <p><strong>CIN / N° Étudiant :</strong> _______________________</p>
            <p><strong>Nom :</strong> ____________________________</p>
            <p><strong>Prénom :</strong> __________________________</p>
        </div>
        <div>
            <p><strong>Réf. examen :</strong>
                <span id="lblDocumentExamRef" style="font-family:monospace; font-weight:bold;">#--</span>
            </p>
            <p><strong>Filière :</strong> __________________________</p>
            <p><strong>Date :</strong> ____________________________</p>
        </div>
    </div>
    <div class="print-grid" id="documentGridInversionTarget"></div>
</div>

<script src="/js/qcm-builder.js"></script>
