<?php require_once __DIR__ . '/../../layouts/header.php'; ?>

<div class="wrap">


<main>

<?php
// admin_reclamations.php — Interface administrateur : gestion des réclamations

$notifications = notifications_pour('admin');
$flash         = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Séparer les réclamations en attente des réclamations déjà traitées
$en_attente = array_values(array_filter($reclamations, fn($r) => $r['statut'] === 'en_attente'));
$traitees   = array_values(array_filter($reclamations, fn($r) => $r['statut'] !== 'en_attente'));

$statut_labels = [
    'en_attente'     => ['label' => 'En attente',        'class' => 'badge--yellow'],
    'approuve_admin' => ['label' => 'Transmise au prof',  'class' => 'badge--blue'],
    'refuse_admin'   => ['label' => 'Refusée (admin)',    'class' => 'badge--red'],
    'approuve_prof'  => ['label' => 'Note modifiée',      'class' => 'badge--green'],
    'refuse_prof'    => ['label' => 'Refusée (prof)',     'class' => 'badge--red'],
];
?>

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
                <i class="ti ti-message-report"></i>
            </div>
            <div>
                <h2 class="form-page-title">Gestion des Réclamations</h2>
                <p class="form-page-sub"><?= count($en_attente) ?> réclamation(s) en attente de traitement</p>
            </div>
        </div>

        <!-- ── RÉCLAMATIONS EN ATTENTE ───────────────────────────────────── -->
        <?php foreach ($en_attente as $r): ?>
        <div class="card recl-card" id="recl-<?= $r['id'] ?>">
            <div class="recl-header">
                <div class="recl-meta">
                    <span class="recl-student"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></span>
                    <span class="recl-num"><?= htmlspecialchars($r['num']) ?></span>
                </div>
                <span class="badge badge--yellow">En attente</span>
            </div>
            <div class="recl-body">
                <div class="recl-info-row">
                    <span class="recl-label">Matière</span>
                    <span class="recl-val"><?= htmlspecialchars($r['matiere_nom']) ?></span>
                </div>
                <div class="recl-info-row">
                    <span class="recl-label">Évaluation</span>
                    <span class="recl-val"><?= htmlspecialchars($r['type_eval_label']) ?></span>
                </div>
                <div class="recl-info-row">
                    <span class="recl-label">Note actuelle</span>
                    <span class="recl-val"><?= $r['note_actuelle'] ?>/20</span>
                </div>
                <div class="recl-info-row">
                    <span class="recl-label">Enseignant</span>
                    <span class="recl-val"><?= htmlspecialchars($r['prof_nom']) ?></span>
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
                        onclick="ouvrirApprouver(<?= $r['id'] ?>)">
                    <i class="ti ti-check"></i> Approuver &amp; Transmettre au prof
                </button>
                <button type="button" class="form-btn-secondary form-btn-secondary--red"
                        onclick="ouvrirRefuser(<?= $r['id'] ?>)">
                    <i class="ti ti-x"></i> Refuser
                </button>
            </div>

            <!-- PANEL — APPROUVER -->
            <div id="panel-approuver-<?= $r['id'] ?>" class="recl-panel recl-panel--green" style="display:none;">
                <form method="POST" action="index.php?action=admin-reclamation">
                    <input type="hidden" name="action" value="admin_approuver">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <div class="panel-inner">
                        <p class="panel-confirm-text">
                            <i class="ti ti-info-circle"></i>
                            Cette réclamation sera transmise à <strong><?= htmlspecialchars($r['prof_nom']) ?></strong>.
                        </p>
                        <div class="panel-btns">
                            <button type="submit" class="form-btn-primary">
                                <i class="ti ti-send"></i> Confirmer la transmission
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
                <form method="POST" action="index.php?action=admin-reclamation">
                    <input type="hidden" name="action" value="admin_refuser">
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

        <?php if (empty($en_attente)): ?>
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
                <span class="badge <?= $statut_labels[$r['statut']]['class'] ?? '' ?>">
                    <?= $statut_labels[$r['statut']]['label'] ?? $r['statut'] ?>
                </span>
            </div>
            <div class="recl-body">
                <div class="recl-info-row">
                    <span class="recl-label">Matière</span>
                    <span class="recl-val"><?= htmlspecialchars($r['matiere_nom']) ?> — <?= htmlspecialchars($r['type_eval_label']) ?></span>
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
                    <span class="recl-label">Raison du refus</span>
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
