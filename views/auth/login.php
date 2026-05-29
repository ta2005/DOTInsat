<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — INSAT</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="/css/login.css">
</head>
<body>
<div class="login-wrapper">

    <header class="login-header">
        <a href="/?page=login" class="brand">
            <img src="/resources/logo.svg" alt=".INSAT" class="brand-logo">
        </a>
    </header>

    <main class="login-main">

        <?php 
        // ken fama error mta3 login 
        if (!empty($error)): ?>
        <div class="flash flash--error">
            <i class="ti ti-circle-x"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="form-page-header">
            <div class="form-page-icon">
                <i class="ti ti-lock" aria-hidden="true"></i>
            </div>
            <div>
                <h2 class="form-page-title">Connexion</h2>
                <p class="form-page-sub">Accédez à votre espace INSAT</p>
            </div>
        </div>


        
        <!-- methode post bch yekhou les donne yaayet ll page de traitement mtaa login -->
        <form class="card form-card login-form" method="POST" action="/?page=do-login">

            <div class="form-section">
                <div class="form-section-label">
                    <i class="ti ti-mail"></i> Identifiants
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Adresse email</label>
                    <input class="form-input" type="email" id="email" name="email"
                           placeholder="prenom.nom@insat.tn"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Mot de passe</label>
                    <div class="password-wrapper">
                        <input class="form-input password-input" type="password"
                               id="password" name="password"
                               placeholder="....." required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="ti ti-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>
            </div>



            <div class="form-actions">
                <button type="submit" class="form-btn-primary login-submit">
                    <i class="ti ti-login"></i> Se connecter
                </button>
            </div>

        </form>

    </main>
</div>

<script src="/js/login.js"></script>
</body>
</html>
