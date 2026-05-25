<section class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Communauté Dev Hub</h1>
        <p class="hero-subtitle">Partagez, apprenez et collaborez.</p>
        <div class="role-badge">
            <span class="role-dot" style="background: #28a745;"></span>
            <span>Connecté : <strong><?= htmlspecialchars($config['role']) ?></strong></span>
        </div>
    </div>
</section>

<div class="row row-full" style="margin-top: -30px; position: relative; z-index: 10;">
    <div class="card profile-card admin-profile-card">
        <div class="admin-profile-left">
            <div class="admin-avatar">
                <i class="ti ti-user-circle" aria-hidden="true" style="font-size: 2.5rem;"></i>
            </div>
            <div class="admin-identity">
                <div class="profile-name"><?= htmlspecialchars($config['profile']['name']) ?></div>
                <div class="admin-title"><?= htmlspecialchars($config['profile']['title']) ?></div>
            </div>
        </div>
        <div class="year-badge"><?= htmlspecialchars($config['profile']['joined']) ?></div>
    </div>
</div>
