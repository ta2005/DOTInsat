<?php
// admin_demandes.php — Interface administrateur : gestion des demandes administratives
session_start();
require_once __DIR__ . '/config/storage.php';
storage_init();

$config        = require __DIR__ . '/config/administrateur.php';
$demandes      = demandes_all();
$notifications = notifications_pour('admin');
$flash         = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Séparer les demandes en attente des demandes déjà traitées
$en_attente = array_values(array_filter($demandes, fn($d) => $d['statut'] === 'en_attente'));
$traitees   = array_values(array_filter($demandes, fn($d) => $d['statut'] !== 'en_attente'));

$statut_labels = [
    'en_attente' => ['label' => 'En attente', 'class' => 'badge--yellow'],
    'approuve'   => ['label' => 'Approuvée',  'class' => 'badge--green'],
    'refuse'     => ['label' => 'Refusée',    'class' => 'badge--red'],
];

$type_icons = [
    'attestation' => 'ti-certificate',
    'stage'       => 'ti-briefcase',
    'salle'       => 'ti-building',
    'autre'       => 'ti-file-description',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes — Admin INSAT</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/forms.css">
    <link rel="stylesheet" href="css/notifications.css">
</head>
<body>
<div class="wrap">

    <!-- NAVBAR ADMIN -->
    <header>
        <nav class="topbar">
            <a href="index.php" class="brand">
                <img src="resources/logo.svg" alt=".INSAT" class="brand-logo">
            </a>
            <ul class="nav">
                <?php foreach ($config['nav'] as $item): ?>
                <li>
                    <?php
                        $isDem    = $item['href'] === 'admin_demandes.php';
                        $isActive = !empty($item['active']) || $isDem;
                    ?>
                    <a href="<?= htmlspecialchars($item['href']) ?>"
                       <?= $isActive ? 'class="active"' : '' ?>>
                        <?= htmlspecialchars($item['label']) ?>
                        <?php if ($isDem && !empty($en_attente)): ?>
                        <span class="nav-badge"><?= count($en_attente) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
            <span class="connect-btn" style="cursor:default;">Admin</span>
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

        <!-- NOTIFICATIONS ADMIN -->
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
                <i class="ti ti-clipboard-list" aria-hidden="true"></i>
            </div>
            <div>
                <h2 class="form-page-title">Gestion des Demandes</h2>
                <p class="form-page-sub"><?= count($en_attente) ?> demande(s) en attente de traitement</p>
            </div>
        </div>

        <!-- ── DEMANDES EN ATTENTE ──────────────────────────────────────────── -->
        <?php foreach ($en_attente as $d): ?>
        <?php $icon = $type_icons[$d['type']] ?? 'ti-file-description'; ?>
        <div class="card recl-card" id="dem-<?= $d['id'] ?>">
            <div class="recl-header">
                <div class="recl-meta">
                    <span class="recl-student"><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></span>
                    <span class="recl-num"><?= htmlspecialchars($d['num']) ?></span>
                </div>
                <span class="badge badge--yellow">En attente</span>
            </div>
            <div class="recl-body">
                <div class="recl-info-row">
                    <span class="recl-label">Type de demande</span>
                    <span class="recl-val">
                        <i class="ti <?= $icon ?>" style="margin-right:5px;opacity:.7;"></i>
                        <?= htmlspecialchars($d['type_label']) ?>
                    </span>
                </div>
                <?php if (!empty($d['commentaire'])): ?>
                <div class="recl-info-row recl-info-row--full">
                    <span class="recl-label">Message de l'étudiant</span>
                    <span class="recl-val"><?= htmlspecialchars($d['commentaire']) ?></span>
                </div>
                <?php endif; ?>
                <span class="recl-date"><?= $d['date_soumission'] ?></span>
            </div>

            <!-- BOUTONS DÉCISION -->
            <div class="recl-actions">
                <button type="button" class="form-btn-primary"
                        onclick="ouvrirApprouver(<?= $d['id'] ?>)">
                    <i class="ti ti-check"></i> Approuver
                </button>
                <button type="button" class="form-btn-secondary form-btn-secondary--red"
                        onclick="ouvrirRefuser(<?= $d['id'] ?>)">
                    <i class="ti ti-x"></i> Refuser
                </button>
            </div>

            <!-- PANEL — APPROUVER -->
            <div id="panel-approuver-<?= $d['id'] ?>" class="recl-panel recl-panel--green" style="display:none;">
                <form method="POST" action="traitement_demande_admin.php">
                    <input type="hidden" name="action" value="approuver">
                    <input type="hidden" name="id"     value="<?= $d['id'] ?>">
                    <div class="panel-inner">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ti ti-message-check"></i> Réponse / Instructions (optionnel)
                            </label>
                            <textarea class="form-textarea" name="reponse_admin" rows="3"
                                      placeholder="Ajoutez des précisions ou instructions pour l'étudiant..."></textarea>
                        </div>
                        <div class="panel-btns">
                            <button type="submit" class="form-btn-primary">
                                <i class="ti ti-check"></i> Confirmer l'approbation
                            </button>
                            <button type="button" class="form-btn-secondary"
                                    onclick="fermerPanels(<?= $d['id'] ?>)">
                                Annuler
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- PANEL — REFUSER -->
            <div id="panel-refuser-<?= $d['id'] ?>" class="recl-panel recl-panel--red" style="display:none;">
                <form method="POST" action="traitement_demande_admin.php">
                    <input type="hidden" name="action" value="refuser">
                    <input type="hidden" name="id"     value="<?= $d['id'] ?>">
                    <div class="panel-inner">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="ti ti-message-x"></i> Raison du refus (obligatoire)
                            </label>
                            <textarea class="form-textarea" name="raison_refus" rows="3"
                                      placeholder="Expliquez pourquoi la demande est refusée..."
                                      required></textarea>
                        </div>
                        <div class="panel-btns">
                            <button type="submit" class="form-btn-secondary form-btn-secondary--red">
                                <i class="ti ti-send"></i> Confirmer le refus
                            </button>
                            <button type="button" class="form-btn-secondary"
                                    onclick="fermerPanels(<?= $d['id'] ?>)">
                                Annuler
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($en_attente)): ?>
        <div class="empty-state">
            <i class="ti ti-circle-check"></i>
            <p>Aucune demande en attente.</p>
        </div>
        <?php endif; ?>

        <!-- ── HISTORIQUE DES DEMANDES TRAITÉES ─────────────────────────────── -->
        <?php if (!empty($traitees)): ?>
        <div class="recl-section-title recl-section-title--muted" style="margin-top:32px;">
            <i class="ti ti-archive"></i> Demandes traitées
        </div>
        <?php foreach ($traitees as $d): ?>
        <?php $icon = $type_icons[$d['type']] ?? 'ti-file-description'; ?>
        <div class="card recl-card recl-card--muted">
            <div class="recl-header">
                <div class="recl-meta">
                    <span class="recl-student"><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></span>
                    <span class="recl-num"><?= htmlspecialchars($d['num']) ?></span>
                </div>
                <span class="badge <?= $statut_labels[$d['statut']]['class'] ?? '' ?>">
                    <?= $statut_labels[$d['statut']]['label'] ?? $d['statut'] ?>
                </span>
            </div>
            <div class="recl-body">
                <div class="recl-info-row">
                    <span class="recl-label">Type de demande</span>
                    <span class="recl-val">
                        <i class="ti <?= $icon ?>" style="margin-right:5px;opacity:.7;"></i>
                        <?= htmlspecialchars($d['type_label']) ?>
                    </span>
                </div>
                <?php if (!empty($d['reponse_admin'])): ?>
                <div class="recl-info-row recl-info-row--full">
                    <span class="recl-label">Réponse de l'admin</span>
                    <span class="recl-val recl-val--green"><?= htmlspecialchars($d['reponse_admin']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($d['raison_refus'])): ?>
                <div class="recl-info-row recl-info-row--full">
                    <span class="recl-label">Raison du refus</span>
                    <span class="recl-val"><?= htmlspecialchars($d['raison_refus']) ?></span>
                </div>
                <?php endif; ?>
                <span class="recl-date"><?= $d['date_soumission'] ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

    </main>
</div>

<script>
function ouvrirApprouver(id) {
    fermerPanels(id);
    const panel = document.getElementById('panel-approuver-' + id);
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
    const a = document.getElementById('panel-approuver-' + id);
    const r = document.getElementById('panel-refuser-'  + id);
    if (a) a.style.display = 'none';
    if (r) r.style.display = 'none';
}
</script>
</body>
</html>
