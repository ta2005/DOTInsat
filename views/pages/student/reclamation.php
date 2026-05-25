<?php
// reclamation.php — Formulaire de réclamation de note (vue étudiant)
session_start();
require_once __DIR__ . '/config/storage.php';
storage_init();

$config      = require __DIR__ . '/config/reclamation.php';
$etudiant_id = 'etudiant_1'; // à remplacer par $_SESSION['user_id'] avec une vraie auth
$notifications = array_values(notifications_pour($etudiant_id));
$flash         = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Préparer les données notes et profs pour le JS (affichage dynamique)
$notesJS = [];
$profsJS = [];
foreach ($config['matieres'] as $m) {
    $notesJS[$m['id']] = ['ds' => $m['ds'], 'examen' => $m['examen']];
    $profsJS[$m['id']] = $m['prof'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réclamation — INSAT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
<div class="wrap">

    <!-- NAVBAR -->
    <header>
        <nav class="topbar">
            <a href="index.php" class="brand">
                <img src="resources/logo.svg" alt=".INSAT" class="brand-logo">
            </a>
            <ul class="nav">
                <?php foreach ($config['nav'] as $item): ?>
                <li>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       <?= !empty($item['active']) ? 'class="active"' : '' ?>>
                        <?= htmlspecialchars($item['label']) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <a href="login.php" class="connect-btn">Connecter</a>
        </nav>
        <div class="brush-divider"></div>
    </header>

    <main>

        <!-- FLASH MESSAGE -->
        <?php if ($flash): ?>
        <div class="flash flash--<?= $flash['type'] ?>">
            <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <!-- NOTIFICATIONS ÉTUDIANT -->
        <?php if (!empty($notifications)): ?>
        <div class="notif-section">
            <div class="notif-section-title">
                <i class="ti ti-bell"></i> Notifications
                <span class="notif-badge"><?= count($notifications) ?></span>
            </div>
            <?php foreach ($notifications as $n): ?>
            <div class="notif-item <?= $n['lu'] ? 'notif-item--lu' : '' ?>">
                <i class="ti ti-info-circle notif-icon"></i>
                <div class="notif-content">
                    <p class="notif-msg"><?= htmlspecialchars($n['message']) ?></p>
                    <span class="notif-date"><?= $n['date'] ?> · expire <?= $n['expiration'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- EN-TÊTE PAGE -->
        <div class="form-page-header">
            <div class="form-page-icon form-page-icon--red">
                <i class="ti ti-message-report" aria-hidden="true"></i>
            </div>
            <div>
                <h2 class="form-page-title">Nouvelle Réclamation</h2>
                <p class="form-page-sub">Signalez un problème concernant une note</p>
            </div>
        </div>

        <form class="card form-card" method="POST" action="traitement_reclamation.php">
            <input type="hidden" name="action" value="soumettre">

            <!-- INFORMATIONS PERSONNELLES -->
            <div class="form-section">
                <div class="form-section-label">
                    <i class="ti ti-user"></i> Informations personnelles
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="rec-nom">Nom</label>
                        <input class="form-input" type="text" id="rec-nom" name="nom"
                               placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="rec-prenom">Prénom</label>
                        <input class="form-input" type="text" id="rec-prenom" name="prenom"
                               placeholder="Votre prénom" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="rec-num">Numéro d'inscription</label>
                    <input class="form-input" type="text" id="rec-num" name="num_inscription"
                           placeholder="Ex: 2024GL1234" required>
                </div>
            </div>

            <!-- MATIÈRE -->
            <div class="form-section">
                <div class="form-section-label">
                    <i class="ti ti-book"></i> Matière concernée
                </div>

                <div class="form-group">
                    <label class="form-label" for="rec-matiere">Sélectionner la matière</label>
                    <div class="form-select-wrapper">
                        <select class="form-select" id="rec-matiere" name="matiere"
                                onchange="onMatiereChange(this)" required>
                            <option value="" disabled selected>Choisir une matière</option>
                            <?php foreach ($config['matieres'] as $m): ?>
                                <option value="<?= htmlspecialchars($m['id']) ?>">
                                    <?= htmlspecialchars($m['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div id="eval-block" class="form-group form-slide" style="display:none;">
                    <label class="form-label" for="rec-eval">Type d'évaluation</label>
                    <div class="form-select-wrapper">
                        <select class="form-select" id="rec-eval" name="type_eval"
                                onchange="onEvalChange()">
                            <option value="" disabled selected>Choisir</option>
                            <?php foreach ($config['types_eval'] as $t): ?>
                                <option value="<?= htmlspecialchars($t['value']) ?>">
                                    <?= htmlspecialchars($t['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Aperçu de la note et de l'enseignant -->
                <div id="note-info-block" class="note-info-card form-slide" style="display:none;">
                    <div class="note-info-row">
                        <div class="note-info-item">
                            <span class="note-info-label">Note obtenue</span>
                            <span class="note-info-val" id="note-val">—</span>
                        </div>
                        <div class="note-info-divider"></div>
                        <div class="note-info-item">
                            <span class="note-info-label">Enseignant responsable</span>
                            <span class="note-info-val note-info-prof" id="prof-val">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMMENTAIRE -->
            <div class="form-section">
                <div class="form-section-label">
                    <i class="ti ti-message"></i> Commentaire
                </div>
                <div class="form-group">
                    <label class="form-label" for="rec-commentaire">Motif de la réclamation</label>
                    <textarea class="form-textarea" id="rec-commentaire" name="commentaire"
                              placeholder="Expliquez la raison de votre réclamation..."
                              rows="4" required></textarea>
                </div>
            </div>

            <!-- ACTIONS -->
            <div class="form-actions">
                <a href="index.php" class="form-btn-secondary">
                    <i class="ti ti-arrow-left"></i> Annuler
                </a>
                <button type="submit" class="form-btn-primary form-btn-primary--red">
                    <i class="ti ti-send"></i> Envoyer la réclamation
                </button>
            </div>

        </form>
    </main>
</div>

<script>
const notesData = <?= json_encode($notesJS) ?>;
const profsData = <?= json_encode($profsJS) ?>;
let currentMatiere = null;

function show(id) {
    const el = document.getElementById(id);
    el.style.display = 'block';
    setTimeout(() => el.classList.add('form-slide-in'), 10);
}
function hide(id) {
    const el = document.getElementById(id);
    el.classList.remove('form-slide-in');
    setTimeout(() => el.style.display = 'none', 200);
}

function onMatiereChange(sel) {
    currentMatiere = sel.value;
    document.getElementById('rec-eval').value = '';
    hide('note-info-block');
    show('eval-block');
}

function onEvalChange() {
    const evalVal = document.getElementById('rec-eval').value;
    if (!currentMatiere || !evalVal || evalVal === 'tp') {
        hide('note-info-block');
        return;
    }
    const note = notesData[currentMatiere]?.[evalVal];
    const prof = profsData[currentMatiere];
    if (note !== undefined) {
        document.getElementById('note-val').textContent  = note + ' / 20';
        document.getElementById('prof-val').textContent  = prof ?? '—';
        show('note-info-block');
    } else {
        hide('note-info-block');
    }
}
</script>
</body>
</html>
