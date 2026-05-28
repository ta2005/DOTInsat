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
</main>
</div>

<script src="/js/demande.js"></script>
