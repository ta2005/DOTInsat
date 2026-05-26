<?php require_once BASE_PATH . '/views/layouts/header.php'; ?>

<div class="wrap">
<main>

<?php
$etudiant_id   = 'etudiant_' . ($_SESSION['user_id'] ?? 1);
$notifications = function_exists('notifications_pour')
    ? array_values(notifications_pour($etudiant_id) ?? [])
    : [];
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$notesJS = [];
$profsJS = [];
foreach ($matieres as $m) {
    $notesJS[$m['id']] = ['ds' => $m['ds'], 'examen' => $m['examen']];
    $profsJS[$m['id']] = $m['prof'];
}
?>

<!-- FLASH MESSAGE -->
<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>">
    <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- NOTIFICATIONS -->
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
            <span class="notif-date"><?= $n['date'] ?><?= !empty($n['expiration']) ? ' · expire ' . $n['expiration'] : '' ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- HEADER PAGE -->
<div class="form-page-header">
    <div class="form-page-icon form-page-icon--red">
        <i class="ti ti-message-report"></i>
    </div>
    <div>
        <h2 class="form-page-title">Nouvelle Réclamation</h2>
        <p class="form-page-sub">Signalez un problème concernant une note</p>
    </div>
</div>

<form class="card form-card" method="POST" action="/?page=save-reclamation">

    <!-- INFOS -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-user"></i> Informations personnelles
        </div>
        <div class="form-grid-2">
            <div class="form-group">
                <label class="form-label">Nom</label>
                <input class="form-input" type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label class="form-label">Prénom</label>
                <input class="form-input" type="text" name="prenom" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Numéro d'inscription</label>
            <input class="form-input" type="text" name="num_inscription" required>
        </div>
    </div>

    <!-- MATIERE -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-book"></i> Matière concernée
        </div>
        <div class="form-group">
            <label class="form-label">Sélectionner la matière</label>
            <select class="form-select" name="matiere" onchange="onMatiereChange(this)" required>
                <option value="" disabled selected>Choisir une matière</option>
                <?php foreach ($matieres as $m): ?>
                    <option value="<?= htmlspecialchars($m['id']) ?>">
                        <?= htmlspecialchars($m['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="eval-block" class="form-group" style="display:none;">
            <label class="form-label">Type d'évaluation</label>
            <select class="form-select" id="rec-eval" name="type_eval" onchange="onEvalChange()">
                <option value="" disabled selected>Choisir</option>
                <?php foreach ($typesEval as $t): ?>
                    <option value="<?= htmlspecialchars($t['value']) ?>">
                        <?= htmlspecialchars($t['label']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="note-info-block" class="note-info-card" style="display:none;">
            <div class="note-info-row">
                <div class="note-info-item">
                    <span class="note-info-label">Note obtenue</span>
                    <span class="note-info-val" id="note-val">—</span>
                </div>
                <div class="note-info-divider"></div>
                <div class="note-info-item">
                    <span class="note-info-label">Enseignant</span>
                    <span class="note-info-val" id="prof-val">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- COMMENTAIRE -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-message"></i> Commentaire
        </div>
        <textarea class="form-textarea" name="commentaire" rows="4" required></textarea>
    </div>

    <!-- ACTIONS -->
    <div class="form-actions">
        <a href="/?page=home" class="form-btn-secondary">Annuler</a>
        <button type="submit" class="form-btn-primary form-btn-primary--red">
            Envoyer
        </button>
    </div>

</form>

</main>
</div>

<script>
const notesData = <?= json_encode($notesJS) ?>;
const profsData = <?= json_encode($profsJS) ?>;
let currentMatiere = null;

function onMatiereChange(sel) {
    currentMatiere = sel.value;
    document.getElementById('rec-eval').value = '';
    document.getElementById('note-info-block').style.display = 'none';
    document.getElementById('eval-block').style.display = 'block';
}

function onEvalChange() {
    const evalVal = document.getElementById('rec-eval').value;
    if (!currentMatiere || !evalVal) return;

    const note = notesData[currentMatiere]?.[evalVal];
    const prof = profsData[currentMatiere];

    if (note !== undefined) {
        document.getElementById('note-val').textContent = note + ' / 20';
        document.getElementById('prof-val').textContent = prof ?? '—';
        document.getElementById('note-info-block').style.display = 'block';
    }
}
</script>