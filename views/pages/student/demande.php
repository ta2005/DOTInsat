<?php require_once BASE_PATH . '/views/layouts/header.php'; ?>

<div class="wrap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<main>

<?php
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>">
    <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- EN-TÊTE PAGE -->
<div class="form-page-header">
    <div class="form-page-icon">
        <i class="ti ti-file-description" aria-hidden="true"></i>
    </div>
    <div>
        <h2 class="form-page-title">Nouvelle Demande</h2>
        <p class="form-page-sub">Remplissez le formulaire ci-dessous</p>
    </div>
</div>

<form class="card form-card" method="POST" action="/?page=save-demande">

    <!-- TYPE DE DEMANDE -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-list-details"></i> Type de demande
        </div>
        <div class="form-group">
            <label class="form-label" for="dem-type">Sélectionner le type</label>
            <div class="form-select-wrapper">
                <select class="form-select" id="dem-type" name="type"
                        onchange="toggleAutre(this)" required>
                    <option value="" disabled selected>Choisir un type de demande</option>
                    <?php foreach ($typesDemande as $t): ?>
                        <option value="<?= htmlspecialchars($t['value']) ?>">
                            <?= htmlspecialchars($t['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div id="autre-field" class="form-group form-slide" style="display:none;">
            <label class="form-label" for="dem-autre">Précisez le type de demande</label>
            <input class="form-input" type="text" id="dem-autre" name="autre_type"
                   placeholder="Décrivez votre demande...">
        </div>
    </div>

    <!-- COMMENTAIRE -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-message"></i> Commentaire
        </div>
        <div class="form-group">
            <label class="form-label" for="dem-commentaire">Message (optionnel)</label>
            <textarea class="form-textarea" id="dem-commentaire" name="message"
                      placeholder="Ajoutez des précisions ou informations complémentaires..."
                      rows="4"></textarea>
        </div>
    </div>

    <!-- ACTIONS -->
    <div class="form-actions">
        <a href="/?page=home" class="form-btn-secondary">
            <i class="ti ti-arrow-left"></i> Annuler
        </a>
        <button type="submit" class="form-btn-primary">
            <i class="ti ti-send"></i> Envoyer la demande
        </button>
    </div>

</form>

<!-- ═══════════════════════════════════════════════════════
     MES DEMANDES — historique de l'étudiant
═══════════════════════════════════════════════════════ -->
<?php
$statutConfig = [
    'EN_ATTENTE' => ['label' => 'En attente', 'class' => 'badge--yellow'],
    'ACCEPTEE'   => ['label' => 'Acceptée',   'class' => 'badge--green'],
    'REFUSEE'    => ['label' => 'Refusée',    'class' => 'badge--red'],
];
$typesLabels = [
    'ATTESTATION_DE_INSCRIPTION' => "Attestation d'inscription",
    'ATTESTATION_DE_PRESENCE'    => "Attestation de présence",
    'FEUILLES_DE_STAGE'          => "Feuilles de stage",
    'AUTRE'                      => "Autre",
];
?>

<?php if (!empty($mesDemandes)): ?>
<div class="form-page-header" style="margin-top:32px;">
    <div class="form-page-icon">
        <i class="ti ti-history"></i>
    </div>
    <div>
        <h2 class="form-page-title">Mes demandes</h2>
        <p class="form-page-sub"><?= count($mesDemandes) ?> demande(s) soumise(s)</p>
    </div>
</div>

<?php foreach ($mesDemandes as $d): ?>
<?php $s = $statutConfig[$d['statut']] ?? ['label' => $d['statut'], 'class' => 'badge--yellow']; ?>
<div class="card recl-card">

    <div class="recl-header">
        <div class="recl-meta">
            <span class="recl-student">
                <?= htmlspecialchars($typesLabels[$d['type']] ?? $d['type_label']) ?>
            </span>
        </div>
        <span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
    </div>

    <div class="recl-body">
        <?php if (!empty($d['message'])): ?>
        <div class="recl-info-row recl-info-row--full">
            <span class="recl-label">Message</span>
            <span class="recl-val"><?= htmlspecialchars($d['message']) ?></span>
        </div>
        <?php endif; ?>

        <span class="recl-date"><?= $d['date_soumission'] ?></span>

        <?php if ($d['statut'] === 'EN_ATTENTE'): ?>
        <form method="POST" action="/?page=delete-demande" style="margin-top:8px;">
            <input type="hidden" name="id" value="<?= $d['id'] ?>">
            <button type="submit" class="form-btn-secondary"
                    onclick="return confirm('Supprimer cette demande ?')">
                <i class="ti ti-trash"></i> Supprimer
            </button>
        </form>
        <?php endif; ?>
    </div>

</div>
<?php endforeach; ?>
<?php endif; ?>

</main>
</div>

<script src="/js/demande.js"></script>
