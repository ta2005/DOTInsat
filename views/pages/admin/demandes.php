<?php
require_once __DIR__ . '/../../layouts/header.php'; 
?><div class="page-content"><?php

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$en_attente = array_values(array_filter($demandes, fn($d) => strtolower($d['statut']) === 'en_attente'));
$traitees   = array_values(array_filter($demandes, fn($d) => strtolower($d['statut']) !== 'en_attente'));

$statut_labels = [
    'EN_ATTENTE' => ['label' => 'En attente', 'class' => 'badge--yellow'],
    'ACCEPTEE'   => ['label' => 'Approuvée',  'class' => 'badge--green'],
    'REFUSEE'    => ['label' => 'Refusée',    'class' => 'badge--red'],
];

$type_icons = [
    'ATTESTATION_DE_INSCRIPTION' => 'ti-certificate',
    'ATTESTATION_DE_PRESENCE'    => 'ti-certificate',
    'FEUILLES_DE_STAGE'          => 'ti-briefcase',
    'FEUILLES_DE_NOTES'          => 'ti-file-text',
    'AUTRES'                     => 'ti-file-description',
];

$type_labels = [
    'ATTESTATION_DE_INSCRIPTION' => "Attestation d'inscription",
    'ATTESTATION_DE_PRESENCE'    => "Attestation de présence",
    'FEUILLES_DE_STAGE'          => "Feuilles de stage",
    'FEUILLES_DE_NOTES'          => "Feuilles de notes",
    'AUTRES'                     => "Autre",
];
?>

<!-- flash -->
<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>">
    <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- header -->
<div class="form-page-header">
    <div class="form-page-icon">
        <i class="ti ti-clipboard-list" aria-hidden="true"></i>
    </div>
    <div>
        <h2 class="form-page-title">Gestion des Demandes</h2>
        <p class="form-page-sub"><?= count($en_attente) ?> demande(s) en attente de traitement</p>
    </div>
</div>

<!-- en atente -->
<?php foreach ($en_attente as $d): ?>
<?php $icon  = $type_icons[$d['type']]   ?? 'ti-file-description'; ?>
<?php $label = $type_labels[$d['type']]  ?? $d['type']; ?>
<div class="card recl-card" id="dem-<?= $d['id'] ?>">
    <div class="recl-header">
        <div class="recl-meta">
            <span class="recl-student"><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></span>
        </div>
        <span class="badge badge--yellow">En attente</span>
    </div>
    <div class="recl-body">
        <div class="recl-info-row">
            <span class="recl-label">Type de demande</span>
            <span class="recl-val">
                <i class="ti <?= $icon ?>" style="margin-right:5px;opacity:.7;"></i>
                <?= htmlspecialchars($label) ?>
            </span>
        </div>
        <?php if (!empty($d['message'])): ?>
        <div class="recl-info-row recl-info-row--full">
            <span class="recl-label">Message de l'étudiant</span>
            <span class="recl-val"><?= htmlspecialchars($d['message']) ?></span>
        </div>
        <?php endif; ?>
        <span class="recl-date"><?= $d['date_soumission'] ?? $d['date_creation'] ?? '' ?></span>
    </div>

    <div class="recl-actions">
        <button type="button" class="form-btn-primary" onclick="ouvrirApprouver(<?= $d['id'] ?>)">
            <i class="ti ti-check"></i> Approuver
        </button>
        <button type="button" class="form-btn-secondary form-btn-secondary--red" onclick="ouvrirRefuser(<?= $d['id'] ?>)">
            <i class="ti ti-x"></i> Refuser
        </button>
    </div>

    <!-- panel approuver -->
    <div id="panel-approuver-<?= $d['id'] ?>" class="recl-panel recl-panel--green" style="display:none;">
        <form method="POST" action="/?page=update-demande-status">
            <input type="hidden" name="id"     value="<?= $d['id'] ?>">
            <input type="hidden" name="statut" value="ACCEPTEE">
            <div class="panel-inner">
                <div class="panel-btns">
                    <button type="submit" class="form-btn-primary">
                        <i class="ti ti-check"></i> Confirmer l'approbation
                    </button>
                    <button type="button" class="form-btn-secondary" onclick="fermerPanels(<?= $d['id'] ?>)">
                        Annuler
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- panel refuser -->
    <div id="panel-refuser-<?= $d['id'] ?>" class="recl-panel recl-panel--red" style="display:none;">
        <form method="POST" action="/?page=update-demande-status">
            <input type="hidden" name="id"     value="<?= $d['id'] ?>">
            <input type="hidden" name="statut" value="REFUSEE">
            <div class="panel-inner">
                <div class="panel-btns">
                    <button type="submit" class="form-btn-secondary form-btn-secondary--red">
                        <i class="ti ti-send"></i> Confirmer le refus
                    </button>
                    <button type="button" class="form-btn-secondary" onclick="fermerPanels(<?= $d['id'] ?>)">
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

<!-- historique -->
<?php if (!empty($traitees)): ?>
<div class="recl-section-title recl-section-title--muted" style="margin-top:32px;">
    <i class="ti ti-archive"></i> Demandes traitées
</div>
<?php foreach ($traitees as $d): ?>
<?php
$statut_upper = strtoupper($d['statut']);
$icon  = $type_icons[$d['type']]  ?? 'ti-file-description';
$label = $type_labels[$d['type']] ?? $d['type'];
?>
<div class="card recl-card recl-card--muted">
    <div class="recl-header">
        <div class="recl-meta">
            <span class="recl-student"><?= htmlspecialchars($d['prenom'] . ' ' . $d['nom']) ?></span>
        </div>
        <span class="badge <?= $statut_labels[$statut_upper]['class'] ?? '' ?>">
            <?= $statut_labels[$statut_upper]['label'] ?? $d['statut'] ?>
        </span>
    </div>
    <div class="recl-body">
        <div class="recl-info-row">
            <span class="recl-label">Type</span>
            <span class="recl-val">
                <i class="ti <?= $icon ?>" style="margin-right:5px;opacity:.7;"></i>
                <?= htmlspecialchars($label) ?>
            </span>
        </div>
        <span class="recl-date"><?= $d['date_soumission'] ?? $d['date_creation'] ?? '' ?></span>
    </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<script src="/js/admin-panels.js"></script>
<?php ?></div><?php
