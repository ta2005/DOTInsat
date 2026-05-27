<?php
// views/pages/professor/examens-prof.php
?>

<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<div class="calculator-page">
<div class="container">
<div class="calculator-wrapper">

    <div class="calculator-header">

        <h1 class="calculator-title">Examens</h1>

        <p class="calculator-subtitle">
            Choisissez une action
        </p>

    </div>

    <div class="semesters-grid" style="grid-template-columns: repeat(3, 1fr); margin-top: 32px;">

        <!-- DASHBOARD -->
        <a href="/?page=qcm-dashboard" style="text-decoration:none;">
            <div class="semester-card" style="cursor:pointer; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:18px; min-height:220px;">
                <span style="font-size:42px; color:#ffffff; opacity:0.7;">
                    <i class="ti ti-layout-dashboard"></i>
                </span>
                <div>
                    <div style="font-size:18px; font-weight:800; color:#ffffff; letter-spacing:-0.5px;">Dashboard</div>
                    <div style="font-size:14px; color:#9a9aa0; margin-top:6px;">Voir tous les examens par cours</div>
                </div>
            </div>
        </a>

        <!-- QCM CREATE -->
        <a href="/?page=qcm-create" style="text-decoration:none;">
            <div class="semester-card" style="cursor:pointer; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:18px; min-height:220px;">
                <span style="font-size:42px; color:#ffffff; opacity:0.7;">
                    <i class="ti ti-file-plus"></i>
                </span>
                <div>
                    <div style="font-size:18px; font-weight:800; color:#ffffff; letter-spacing:-0.5px;">Créer QCM</div>
                    <div style="font-size:14px; color:#9a9aa0; margin-top:6px;">Construire la clé de correction</div>
                </div>
            </div>
        </a>

        <!-- QCM SCAN -->
        <a href="/?page=qcm-scan" style="text-decoration:none;">
            <div class="semester-card" style="cursor:pointer; text-align:center; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:18px; min-height:220px;">
                <span style="font-size:42px; color:#ffffff; opacity:0.7;">
                    <i class="ti ti-scan"></i>
                </span>
                <div>
                    <div style="font-size:18px; font-weight:800; color:#ffffff; letter-spacing:-0.5px;">Scanner QCM</div>
                    <div style="font-size:14px; color:#9a9aa0; margin-top:6px;">Analyser les feuilles étudiants</div>
                </div>
            </div>
        </a>

    </div>

</div>
</div>
</div>
