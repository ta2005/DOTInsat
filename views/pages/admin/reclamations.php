<?php
// views/pages/admin/reclamations.php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$en_attente = array_values(array_filter($reclamations, fn($r) => strtoupper($r['statut']) === 'EN_ATTENTE'));
$traitees   = array_values(array_filter($reclamations, fn($r) => strtoupper($r['statut']) !== 'EN_ATTENTE'));

$statut_labels = [
    'EN_ATTENTE' => ['label' => 'En attente',       'class' => 'badge--yellow'],
    'ACCEPTEE'   => ['label' => 'Transmise au prof', 'class' => 'badge--green'],
    'REFUSEE'    => ['label' => 'Refusée',           'class' => 'badge--red'],
];
?>

<!-- FLASH -->
<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>">
    <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
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

<!-- ── RÉCLAMATIONS EN ATTENTE ──────────────────────────────────── -->
<?php foreach ($en_attente as $r): ?>
<div class="card recl-card" id="recl-<?= $r['id'] ?>">
    <div class="recl-header">
        <div class="recl-meta">
            <span class="recl-student"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></span>
        </div>
        <span class="badge badge--yellow">En attente</span>
    </div>
    <div class="recl-body">
        <div class="recl-info-row">
            <span class="recl-label">Matière</span>
            <span class="recl-val"><?= htmlspecialchars($r['matiere_nom'] ?? '') ?></span>
        </div>
        <div class="recl-info-row">
            <span class="recl-label">Évaluation</span>
            <span class="recl-val"><?= htmlspecialchars($r['type_eval'] ?? '') ?></span>
        </div>
        <div class="recl-info-row">
            <span class="recl-label">Enseignant</span>
            <span class="recl-val"><?= htmlspecialchars($r['prof_nom'] ?? '') ?></span>
        </div>
        <div class="recl-info-row recl-info-row--full">
            <span class="recl-label">Motif</span>
            <span class="recl-val"><?= htmlspecialchars($r['commentaire'] ?? '') ?></span>
        </div>
        <span class="recl-date"><?= $r['date_soumission'] ?? $r['date_creation'] ?? '' ?></span>
    </div>

    <div class="recl-actions">
        <button type="button" class="form-btn-primary" onclick="ouvrirApprouver(<?= $r['id'] ?>)">
            <i class="ti ti-check"></i> Approuver &amp; Transmettre
        </button>
        <button type="button" class="form-btn-secondary form-btn-secondary--red" onclick="ouvrirRefuser(<?= $r['id'] ?>)">
            <i class="ti ti-x"></i> Refuser
        </button>
    </div>

    <!-- PANEL APPROUVER -->
    <div id="panel-approuver-<?= $r['id'] ?>" class="recl-panel recl-panel--green" style="display:none;">
        <form method="POST" action="/?page=update-reclamation-status">
            <input type="hidden" name="id"     value="<?= $r['id'] ?>">
            <input type="hidden" name="statut" value="ACCEPTEE">
            <div class="panel-inner">
                <p class="panel-confirm-text">
                    <i class="ti ti-info-circle"></i>
                    Cette réclamation sera transmise à <strong><?= htmlspecialchars($r['prof_nom'] ?? '') ?></strong>.
                </p>
                <div class="panel-btns">
                    <button type="submit" class="form-btn-primary">
                        <i class="ti ti-send"></i> Confirmer la transmission
                    </button>
                    <button type="button" class="form-btn-secondary" onclick="fermerPanels(<?= $r['id'] ?>)">
                        Annuler
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- PANEL REFUSER -->
    <div id="panel-refuser-<?= $r['id'] ?>" class="recl-panel recl-panel--red" style="display:none;">
        <form method="POST" action="/?page=update-reclamation-status">
            <input type="hidden" name="id"     value="<?= $r['id'] ?>">
            <input type="hidden" name="statut" value="REFUSEE">
            <div class="panel-inner">
                <div class="panel-btns">
                    <button type="submit" class="form-btn-secondary form-btn-secondary--red">
                        <i class="ti ti-send"></i> Confirmer le refus
                    </button>
                    <button type="button" class="form-btn-secondary" onclick="fermerPanels(<?= $r['id'] ?>)">
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

<!-- ── HISTORIQUE ──────────────────────────────────────────────── -->
<?php if (!empty($traitees)): ?>
<div class="recl-section-title recl-section-title--muted" style="margin-top:32px;">
    <i class="ti ti-archive"></i> Réclamations traitées
</div>
<?php foreach ($traitees as $r): ?>
<?php $statut_upper = strtoupper($r['statut']); ?>
<div class="card recl-card recl-card--muted">
    <div class="recl-header">
        <div class="recl-meta">
            <span class="recl-student"><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></span>
        </div>
        <span class="badge <?= $statut_labels[$statut_upper]['class'] ?? '' ?>">
            <?= $statut_labels[$statut_upper]['label'] ?? $r['statut'] ?>
        </span>
    </div>
    <div class="recl-body">
        <div class="recl-info-row">
            <span class="recl-label">Matière</span>
            <span class="recl-val"><?= htmlspecialchars($r['matiere_nom'] ?? '') ?> — <?= htmlspecialchars($r['type_eval'] ?? '') ?></span>
        </div>
        <span class="recl-date"><?= $r['date_soumission'] ?? $r['date_creation'] ?? '' ?></span>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<script>
function ouvrirApprouver(id) {
    fermerPanels(id);
    const p = document.getElementById('panel-approuver-' + id);
    if (p) { p.style.display = 'block'; p.scrollIntoView({ behavior:'smooth', block:'nearest' }); }
}
function ouvrirRefuser(id) {
    fermerPanels(id);
    const p = document.getElementById('panel-refuser-' + id);
    if (p) { p.style.display = 'block'; p.scrollIntoView({ behavior:'smooth', block:'nearest' }); }
}
function fermerPanels(id) {
    const a = document.getElementById('panel-approuver-' + id);
    const r = document.getElementById('panel-refuser-'   + id);
    if (a) a.style.display = 'none';
    if (r) r.style.display = 'none';
}
</script>
