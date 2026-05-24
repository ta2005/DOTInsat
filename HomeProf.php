<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Espace Enseignant</title>
    <link href="css/Layout.css" rel="stylesheet">
    <link href="css/home.css" rel="stylesheet">
</head>

<body>
    <?php include 'navbar.php'; ?>

    <main class="dashboard">

        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">INSAT All In One Platform</h1>
                <p class="hero-subtitle">Jusqu'ici tout va bien</p>
                <div class="user-status-badge">
                    <span class="status-dot"></span>
                    Connecté en tant que : <strong>Enseignant</strong>
                </div>
            </div>

            <div class="hero-image-frame">
                <img src="resources/insat mel fou9.png" alt="Hero Image" class="hero-image">
            </div>
        </section>
        
        <section class="profile-card prof-control-panel">
            <div class="prof-header-wrapper">
                <div class="prof-identity">
                    <h1>Aymen Sellaouti</h1>
                    <span class="prof-year-badge">2025-2026</span>
                </div>
                
                <form class="class-selector-form" method="POST" action="">
                    <div class="select-wrapper">
                        <select id="class-select" name="selected_class" class="custom-select">
                            <option value="" disabled selected>Choisir une classe</option>
                            <option value="GL2-1">GL2-1</option>
                            <option value="GL2-2">GL2-2</option>
                            <option value="RT2-1">RT2-1</option>
                            <option value="MPI-1">MPI-1</option>
                        </select>
                    </div>
                    <button type="button" class="stats-btn">Charger Statistiques</button>
                </form>
            </div>
            
            <div class="current-showing-label">
                Statistiques actuelles : <span class="active-class">GL2-1</span>
            </div>
        </section>

        <section class="stats-grid">
            <div class="card">
                <span class="card-value large-num">86<span class="total-divider">/116</span></span>
                <span class="card-label">Avancement des Notes</span>
            </div>

            <div class="card">
                <span class="card-value large-num">19,5</span>
                <span class="card-label">Meilleure Note</span>
            </div>

            <div class="card">
                <span class="card-value large-num">12,31</span>
                <span class="card-label">Moyenne de la Classe</span>
            </div>
        </section>

        <section class="chart-section-wrapper">
            <div class="chart-placeholder-card">
                <div class="chart-header">
                    <h3>Distribution Évolutive des Notes</h3>
                    <div class="chart-legend-mock">
                        <span class="legend-item"><span class="color-indicator blue"></span>DS</span>
                        <span class="legend-item"><span class="color-indicator red"></span>Examen</span>
                        <span class="legend-item"><span class="color-indicator gray"></span>Moyenne</span>
                    </div>
                </div>
                <div class="chart-body-area">
                    <p class="placeholder-text">[ Espace réservé pour l'affichage du graphe ]</p>
                </div>
            </div>
        </section>

    </main>
</body>

</html>
