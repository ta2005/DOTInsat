<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>home</title>
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
                <a href="#" class="hero-btn">DoSomething</a>
            </div>

            <div class="hero-image-frame">
                <img src="resources/hero-image.png" alt="Platform Visual" class="hero-img">
            </div>
        </section>

        <section class="profile-card">
            <div class="profile-header">
                <h1>Rayen Khammar</h1>
                <span class="year-badge">25-26</span>
            </div>
            <p class="class-group">GL2-1</p>
        </section>

        <section class="stats-grid">
            <div class="card">
                <span class="card-value large-num">10</span>
                <span class="card-label">Notes Acquises</span>
            </div>

            <div class="card">
                <span class="card-label subtitle">Dernière Réclam:</span>
                <span class="card-value status-text">Traité</span>
            </div>

            <div class="card">
                <span class="card-label subject-title">Java</span>
                <span class="card-value large-num">14</span>
            </div>
        </section>

    </main>
</body>

</html>