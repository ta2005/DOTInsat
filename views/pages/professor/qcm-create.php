<?php
// views/pages/professor/qcm-create.php

<<<<<<< HEAD
$config = [
    'nav' => [
        ['href' => '/?page=home',             'label' => 'Accueil'],
        ['href' => '/?page=forum',             'label' => 'Blog'],
        ['href' => '/?page=examens-prof',      'label' => 'Examens'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];
=======
<head>
    <meta charset="UTF-8">
    <title>Panneau Professeur - Créer un Modèle Maître QCM</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        /* Scoped styles for the Professor QCM Generator Engine */
        .config-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c

require BASE_PATH . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="/css/qcm.css">

<div class="qcm-wrap no-print">

    <h2 class="page-title">Créer une clé de correction QCM</h2>
    <p class="page-sub">Configurez les paramètres puis assignez les bonnes réponses.</p>

<<<<<<< HEAD
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
=======
        /* Grid Layout for Interactive Matrix Mapping Keys */
        #interactiveMatrixWorkspace {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .matrix-item {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s;
        }

        .matrix-item:hover {
            transform: translateY(-2px);
            border-color: #b5b8bd;
        }

        .matrix-header {
            margin: 0 0 12px 0;
            color: #212529;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 8px;
        }

        .choice-row {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 5px;
        }

        .bubble-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-family: monospace;
            cursor: pointer;
            font-size: 1.1rem;
        }

        /* Master Copy Printable Document Styling Layout */
        #printableBubbleDocument {
            display: none;
            background: #fff;
            padding: 50px;
            border: 2px dashed #6c757d;
            border-radius: 6px;
            margin-top: 40px;
        }

        .doc-title-block {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 4px double #000;
            padding-bottom: 20px;
        }

        .doc-title-block h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            letter-spacing: 1px;
        }

        .student-info-grid {
            border: 2px solid #000;
            padding: 20px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
            background: #fafafa;
        }

        .student-info-grid p {
            margin: 10px 0;
            font-size: 1.05rem;
        }

        .sheet-columns-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .sheet-row-line {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-family: monospace;
            font-size: 1.15rem;
        }

        .sheet-question-num {
            width: 45px;
            font-weight: bold;
            color: #000;
        }

        .sheet-bubbles-container {
            display: flex;
            gap: 18px;
        }

        .vector-bubble-circle {
            width: 26px;
            height: 26px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            background: #fff;
        }

        /* Actions styling */
        .control-actions {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            background: #fff;
            padding: 15px 0;
            position: sticky;
            bottom: 0;
            border-top: 1px solid #dee2e6;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #0d6efd;
            color: white;
        }

        .btn-success {
            background-color: #198754;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        /* Print Override Layout Configuration Interceptor */
        @media print {
            body * {
                visibility: hidden;
            }

            #printableBubbleDocument,
            #printableBubbleDocument * {
                visibility: visible;
            }

            #printableBubbleDocument {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                padding: 0;
                margin: 0;
            }

            .no-print-layer {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container no-print-layer" style="padding: 30px; max-width: 1200px; margin: 0 auto;">
        <h2>Créer un Nouveau Modèle de Clé QCM Automatisé</h2>
        <p class="text-muted">Configurez les paramètres structurels de votre feuille d'évaluation. Le moteur compile une
            matrice de réponses interactive et produit un schéma de grille personnalisé correspondant à vos
            configurations.</p>

        <div class="config-card">
            <div class="setup-row">
                <div class="input-wrapper">
                    <label for="controleId">Association du Contexte d'Examen (ID PostgreSQL)</label>
                    <input type="number" id="controleId" min="1" placeholder="ex: 14" required>
                </div>
                <div class="input-wrapper">
                    <label for="totalQuestions">Nombre de Questions (10 - 40)</label>
                    <input type="number" id="totalQuestions" min="10" max="40" value="20" required>
                </div>
                <div class="input-wrapper">
                    <label for="choicesPerQuestion">Options par Question (2 - 5)</label>
                    <input type="number" id="choicesPerQuestion" min="2" max="5" value="4" required>
                </div>
            </div>
            <button type="button" id="btnInitializeWorkspace" class="btn btn-primary">Construire la Matrice de
                Configuration</button>
        </div>

        <form id="qcmMasterKeyForm" style="display:none;">
            <h3>Attribuer les Réponses Cibles et les Pondérations de Points</h3>
            <p class="text-muted" style="margin-bottom: 20px;">Fournissez les valeurs de choix corrects et le
                coefficient de score personnalisé alloué pour chaque ligne de question.</p>

            <div id="interactiveMatrixWorkspace"></div>

            <div class="control-actions">
                <button type="submit" class="btn btn-success">Valider le Schéma de Clé Maître</button>
                <button type="button" id="TriggerPrintJob" class="btn btn-secondary">Imprimer le Modèle de Feuille
                    Vierge</button>
            </div>
        </form>
    </div>

    <div id="printableBubbleDocument">
        <div class="doc-title-block">
            <h1>FEUILLE DE RÉPONSES D'EXAMEN - MODÈLE LISIBLE DE MATRICE BULLES</h1>
            <p>Instructions : Noircissez complètement les cercles avec un stylo foncé. Assurez-vous que vos champs
                d'identité sont explicitement distincts.</p>
        </div>

        <div class="student-info-grid">
            <div>
                <p><strong>NUMÉRO D'ÉTER/SÉRIE :</strong> _______________________</p>
                <p><strong>NOM DE FAMILLE :</strong> ____________________________</p>
                <p><strong>PRÉNOM :</strong> _________________________</p>
            </div>
            <div>
                <p><strong>IDENTIFIANT D'ÉVALUATION :</strong> <span id="lblDocumentExamRef"
                        style="font-family: monospace; font-weight: bold; background: #e9ecef; padding: 2px 6px; border-radius:3px;">#--</span>
                </p>
                <p><strong>CLASSE / FILÈRE :</strong> __________________________</p>
                <p><strong>DATE :</strong> ______________________</p>
            </div>
        </div>
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c

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
