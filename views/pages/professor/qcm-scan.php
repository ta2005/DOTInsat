<?php
// views/pages/professor/qcm-scan.php

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
    <title>Panneau Professeur - Moteur de Scanner QCM Automatisé</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .scanner-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            margin-top: 20px;
        }
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c

require BASE_PATH . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="/css/qcm.css">

<div class="qcm-wrap">

    <h2 class="page-title">Scanner QCM</h2>
    <p class="page-sub">Analysez automatiquement les feuilles de réponses étudiants.</p>

<<<<<<< HEAD
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
=======
        .log-stream {
            max-height: 300px;
            overflow-y: auto;
            background: #212529;
            color: #0dfd53;
            font-family: monospace;
            padding: 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 15px;
        }

        .log-entry {
            margin-bottom: 6px;
            border-bottom: 1px solid #343a40;
            padding-bottom: 4px;
        }

        .score-badge {
            display: inline-block;
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
            margin: 15px 0;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            background: #ffc107;
            color: #000;
        }

        .status-pill.success {
            background: #198754;
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="container" style="padding: 30px; max-width: 1300px; margin: 0 auto;">
        <div
            style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid #e9ecef; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05); display: flex; gap: 20px; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 180px; display: flex; flex-direction: column;">
                <label style="font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; color: #495057;">Sélectionnez le
                    Contexte d'Examen (ID)</label>
                <input type="number" id="scanExamId" min="1" value="14" required
                    style="padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 1rem;">
            </div>
            <div style="flex: 1; min-width: 180px; display: flex; flex-direction: column;">
                <label style="font-weight: 600; margin-bottom: 8px; font-size: 0.9rem; color: #495057;">ID Étudiant
                    (CIN/Série)</label>
                <input type="number" id="scanStudentId" min="1" value="1002" required
                    style="padding: 10px; border: 1px solid #ced4da; border-radius: 4px; font-size: 1rem;">
            </div>
        </div>

        <div class="scanner-layout">

            <div>
                <div id="dropzone" class="dropzone-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0d6efd" viewBox="0 0 16 16"
                        style="margin-bottom:15px;">
                        <path
                            d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                        <path
                            d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z" />
                    </svg>
                    <h4>Glissez et Déposez l'Image de la Feuille Étudiant Ici</h4>
                    <p class="text-muted">Supporte les fichiers PNG, JPG ou JPEG</p>
                    <input type="file" id="fileFallbackInput" accept="image/*" style="display: none;">
                    <button type="button" class="btn btn-primary"
                        onclick="document.getElementById('fileFallbackInput').click()">Parcourir les Fichiers</button>
                </div>

                <canvas id="processingCanvas"></canvas>
            </div>

            <div class="results-sidebar">
                <h3>Console de Traitement</h3>
                <hr>
                <div style="margin-bottom: 15px;">
                    <label><strong>ID de Session Étudiant Actuel :</strong></label>
                    <div id="lblSessionStudent" style="font-size: 1.2rem; font-weight: bold; color: #495057;">Scan en
                        Attente...</div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label><strong>Statut d'Exécution :</strong></label>
                    <div><span id="pillStatus" class="status-pill">En Attente de Charge Utile Cible</span></div>
                </div>

                <div>
                    <label><strong>Score de Note de Retour Calculé :</strong></label>
                    <div><span id="lblCalculatedScore" class="score-badge">0.00</span></div>
                </div>

                <strong>Journal d'Activité du Moteur Système :</strong>
                <div id="logStream" class="log-stream">
                    <div class="log-entry">[Système] Moteur principal actif. En attente du téléchargement d'image...
                    </div>
                </div>
            </div>

        </div>
>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
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
