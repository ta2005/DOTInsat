<?php
// prof_reclamations.php — Interface professeur : traitement des réclamations transmises
session_start();
require_once __DIR__ . '/config/storage.php';
storage_init();

$config        = require __DIR__ . '/config/enseignant.php';
$reclamations  = reclamations_all();
$notifications = array_values(notifications_pour('prof'));
$flash         = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Le professeur ne voit que les réclamations que l'admin lui a transmises
$a_traiter = array_values(array_filter($reclamations, fn($r) => $r['statut'] === 'approuve_admin'));
$traitees  = array_values(array_filter($reclamations, fn($r) =>
    in_array($r['statut'], ['approuve_prof', 'refuse_prof'])
));

$statut_labels = [
    'approuve_prof' => ['label' => 'Note modifiée', 'class' => 'badge--green'],
    'refuse_prof'   => ['label' => 'Refusée',       'class' => 'badge--red'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réclamations — Prof INSAT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
<div class="wrap">

    <!-- NAVBAR PROF -->
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
                <li>
                    <a href="prof_reclamations.php" class="active">
                        Réclamations
                        <?php if (!empty($a_traiter)): ?>
                        <span class="nav-badge"><?= count($a_traiter) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
            <span class="connect-btn" style="cursor:default;">Prof</span>
        </nav>
        <div class="brush-divider"></div>
    </header>

    <main>

        <!-- FLASH -->
        <?php if ($flash): ?>
        <div class="flash flash--<?= $flash['type'] ?>">
            <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <!-- NOTIFICATIONS PROF -->
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

        <!-- EN-TÊTE -->
        <div class="form-page-header">
            <div class="form-page-icon">
                <i class="ti ti-message-report"></i>
            </div>
            <div>
                <h2 class="form-page-title">Réclamations à traiter</h2>
                <p class="form-page-sub"><?= count($a_traiter) ?> demande(s) transmise(s) par l'administration</p>
            </div>
        </div>

        <!-- ── RÉCLAMATIONS À TRAITER ────────────────────────────────────── -->
        <?php foreach ($a_traiter as $r): ?>
        <div class="card recl-card" id="recl-<?= $r['id'] ?>">
            <div class="recl-header">
                <div class="recl-meta">
                    <span class="recl-student"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></span>
                    <span class="recl-num"><?= htmlspecialchars($r['num']) ?></span>
                </div>
                <span class="badge badge--blue">Transmise par admin</span>
            </div>
            <div class="recl-body">
                <div class="recl-info-row">
                    <span class="recl-label">Matière</span>
                    <span class="recl-val"><?= htmlspecialchars($r['matiere_nom']) ?></span>
                </div>
                <div class="recl-info-row">
                    <span class="recl-label">Type</span>
                    <span class="recl-val"><?= htmlspecialchars($r['type_eval_label']) ?></span>
                </div>
                <div class="recl-info-row">
                    <span class="recl-label">Note actuelle</span>
                    <span class="recl-val"><?= $r['note_actuelle'] ?>/20</span>
                </div>
                <div class="recl-info-row recl-info-row--full">
                    <span class="recl-label">Motif de l'étudiant</span>
                    <span class="recl-val"><?= htmlspecialchars($r['commentaire']) ?></span>
                </div>
                <span class="recl-date"><?= $r['date_soumission'] ?></span>
            </div>

            <!-- BOUTONS DÉCISION -->
            <div class="recl-actions">
                <button type="button" class="form-btn-primary"
                        onclick="ouvrirAccepter(<?= $r['id'] ?>)">
                    <i class="ti ti-check"></i> Accepter &amp; Modifier la note
                </button>
                <button type="button" class="form-btn-secondary form-btn-secondary--red"
                        onclick="ouvrirRefuser(<?= $r['id'] ?>)">
                    <i class="ti ti-x"></i> Refuser
                </button>
            </div>

            <!-- PANEL — ACCEPTER -->
            <div id="panel-accepter-<?= $r['id'] ?>" class="recl-panel recl-panel--green" style="display:none;">
                <form method="POST" action="traitement_reclamation.php">
                    <input type="hidden" name="action" value="prof_approuver">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="panel-inner">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ti ti-edit"></i> Nouvelle note (sur 20)
                            </label>
                            <input type="number" class="form-input" name="nouvelle_note"
                                   min="0" max="20" step="0.5"
                                   value="<?= $r['note_actuelle'] ?>"
                                   placeholder="Ex: 14.5" required>
                        </div>
                        <div class="panel-btns">
                            <button type="submit" class="form-btn-primary">
                                <i class="ti ti-device-floppy"></i> Confirmer la nouvelle note
                            </button>
                            <button type="button" class="form-btn-secondary"
                                    onclick="fermerPanels(<?= $r['id'] ?>)">
                                Annuler
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- PANEL — REFUSER -->
            <div id="panel-refuser-<?= $r['id'] ?>" class="recl-panel recl-panel--red" style="display:none;">
                <form method="POST" action="traitement_reclamation.php">
                    <input type="hidden" name="action" value="prof_refuser">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="panel-inner">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ti ti-message-x"></i> Raison du refus (obligatoire)
                            </label>
                            <textarea class="form-textarea" name="raison" rows="3"
                                      placeholder="Expliquez pourquoi la réclamation est refusée..."
                                      required></textarea>
                        </div>
                        <div class="panel-btns">
                            <button type="submit" class="form-btn-secondary form-btn-secondary--red">
                                <i class="ti ti-send"></i> Confirmer le refus
                            </button>
                            <button type="button" class="form-btn-secondary"
                                    onclick="fermerPanels(<?= $r['id'] ?>)">
                                Annuler
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($a_traiter)): ?>
        <div class="empty-state">
            <i class="ti ti-circle-check"></i>
            <p>Aucune réclamation en attente.</p>
        </div>
        <?php endif; ?>

        <!-- ── HISTORIQUE DES RÉCLAMATIONS TRAITÉES ─────────────────────── -->
        <?php if (!empty($traitees)): ?>
        <div class="recl-section-title recl-section-title--muted" style="margin-top:32px;">
            <i class="ti ti-archive"></i> Réclamations traitées
        </div>
        <?php foreach ($traitees as $r): ?>
        <div class="card recl-card recl-card--muted">
            <div class="recl-header">
                <div class="recl-meta">
                    <span class="recl-student"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></span>
                    <span class="recl-num"><?= htmlspecialchars($r['num']) ?></span>
                </div>
                <span class="badge <?= $statut_labels[$r['statut']]['class'] ?>">
                    <?= $statut_labels[$r['statut']]['label'] ?>
                </span>
            </div>
            <div class="recl-body">
                <div class="recl-info-row">
                    <span class="recl-label">Matière</span>
                    <span class="recl-val">
                        <?= htmlspecialchars($r['matiere_nom']) ?> — <?= htmlspecialchars($r['type_eval_label']) ?>
                    </span>
                </div>
                <?php if (!empty($r['note_nouvelle'])): ?>
                <div class="recl-info-row">
                    <span class="recl-label">Note</span>
                    <span class="recl-val">
                        <?= $r['note_actuelle'] ?>/20 → <strong style="color:#4ade80"><?= $r['note_nouvelle'] ?>/20</strong>
                    </span>
                </div>
                <?php endif; ?>
                <?php if (!empty($r['raison_refus'])): ?>
                <div class="recl-info-row">
                    <span class="recl-label">Raison</span>
                    <span class="recl-val"><?= htmlspecialchars($r['raison_refus']) ?></span>
                </div>
                <?php endif; ?>
                <span class="recl-date"><?= $r['date_soumission'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<script>
function ouvrirAccepter(id) {
    fermerPanels(id);
    const panel = document.getElementById('panel-accepter-' + id);
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ouvrirRefuser(id) {
    fermerPanels(id);
    const panel = document.getElementById('panel-refuser-' + id);
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function fermerPanels(id) {
    const a = document.getElementById('panel-accepter-' + id);
    const r = document.getElementById('panel-refuser-'  + id);
    if (a) a.style.display = 'none';
    if (r) r.style.display = 'none';
}
</script>
</body>
</html>
