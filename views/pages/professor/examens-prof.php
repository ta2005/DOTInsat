



<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-base.css">

<div class="calculator-page">
<div class="container">
<div class="calculator-wrapper">

    <div class="calculator-header">

        <h1 class="calculator-title">Examens</h1>

        <p class="calculator-subtitle">
            Choisissez une action
        </p>

    </div>

    <div class="semesters-grid">

        <!-- DASHBOARD -->
        <a href="/?page=qcm-dashboard" class="semester-card-link">
            <div class="semester-card">
                <span class="semester-card-icon">
                    <i class="ti ti-layout-dashboard"></i>
                </span>
                <div>
                    <div class="card-label">Dashboard</div>
                    <div class="card-desc">Voir tous les examens par cours</div>
                </div>
            </div>
        </a>

        <!-- QCM CREATE -->
        <a href="/?page=qcm-create" class="semester-card-link">
            <div class="semester-card">
                <span class="semester-card-icon">
                    <i class="ti ti-file-plus"></i>
                </span>
                <div>
                    <div class="card-label">Créer QCM</div>
                    <div class="card-desc">Construire la clé de correction</div>
                </div>
            </div>
        </a>

        <!-- QCM SCAN -->
        <a href="/?page=qcm-scan" class="semester-card-link">
            <div class="semester-card">
                <span class="semester-card-icon">
                    <i class="ti ti-scan"></i>
                </span>
                <div>
                    <div class="card-label">Scanner QCM</div>
                    <div class="card-desc">Analyser les feuilles étudiants</div>
                </div>
            </div>
        </a>

    </div>

</div>
</div>
</div>
