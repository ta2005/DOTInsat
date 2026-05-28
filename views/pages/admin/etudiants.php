<?php
// views/pages/admin/etudiants.php
?>
<link rel="stylesheet" href="/css/etudiants.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="etu-page">

    <!-- ══ FLASH ══════════════════════════════════════════════════════════ -->
    <?php if ($flash): ?>
    <div class="flash flash--<?= $flash['type'] ?>">
        <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- ══ EN-TÊTE PAGE ═══════════════════════════════════════════════════ -->
    <div class="etu-header">
        <div class="etu-header-icon">
            <i class="ti ti-users-group"></i>
        </div>
        <div>
            <h1 class="etu-header-title">Gestion des Étudiants</h1>
            <p class="etu-header-sub">
                Filtrez par filière et classe pour gérer les étudiants
            </p>
        </div>
    </div>

    <!-- ══ FILTRE ══════════════════════════════════════════════════════════ -->
    <div class="etu-filter-card">

        <form method="GET" action="/" class="etu-filter-form" id="filterForm">

            <input type="hidden" name="page" value="etu_manage">

            <!-- Filière -->
            <div class="etu-filter-group">
                <label class="etu-filter-label" for="sel-filiere">
                    <i class="ti ti-category"></i>
                    Filière
                </label>
                <div class="etu-select-wrap">
                    <select
                        class="etu-select"
                        id="sel-filiere"
                        name="filiere"
                        onchange="updateClasses(this.value)"
                    >
                        <option value="">— Choisir une filière —</option>
                        <?php foreach ($filieres as $f): ?>
                        <option
                            value="<?= htmlspecialchars($f) ?>"
                            <?= $filiere === $f ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($f) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ti ti-chevron-down etu-select-icon"></i>
                </div>
            </div>

            <!-- Classe -->
            <div class="etu-filter-group">
                <label class="etu-filter-label" for="sel-classe">
                    <i class="ti ti-door"></i>
                    Classe
                </label>
                <div class="etu-select-wrap">
                    <select
                        class="etu-select"
                        id="sel-classe"
                        name="classe"
                    >
                        <option value="">— Choisir une classe —</option>
                        <?php
                        $classesInit = $filiere !== ''
                            ? ($classesByFiliere[$filiere] ?? [])
                            : [];
                        foreach ($classesInit as $cl):
                        ?>
                        <option
                            value="<?= htmlspecialchars($cl) ?>"
                            <?= $classe === $cl ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($cl) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="ti ti-chevron-down etu-select-icon"></i>
                </div>
            </div>

            <button type="submit" class="etu-btn-filter">
                <i class="ti ti-search"></i>
                Afficher
            </button>

        </form>

    </div>

    <!-- ══ LISTE ÉTUDIANTS ════════════════════════════════════════════════ -->
    <?php if ($filiere !== '' && $classe !== ''): ?>

    <div class="etu-list-header">

        <div class="etu-list-meta">
            <span class="etu-badge-filiere"><?= htmlspecialchars($filiere) ?></span>
            <span class="etu-sep">·</span>
            <span class="etu-classe-label"><?= htmlspecialchars($classe) ?></span>
            <span class="etu-count">
                <?= count($etudiants) ?> étudiant<?= count($etudiants) !== 1 ? 's' : '' ?>
            </span>
        </div>

    </div>

    <!-- TABLE -->
    <?php if (!empty($etudiants)): ?>

    <div class="etu-table-wrap">
        <table class="etu-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>CIN</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Niveau</th>
                    <th>Année</th>
                    <th class="etu-th-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $i => $e): ?>
                <tr class="etu-row" id="row-<?= $e['id'] ?>">

                    <td class="etu-td-num"><?= $i + 1 ?></td>

                    <td class="etu-td-cin">
                        <?= $e['cin'] ? htmlspecialchars((string)$e['cin']) : '<span class="etu-empty">—</span>' ?>
                    </td>

                    <td class="etu-td-name">
                        <span class="etu-avatar">
                            <?= mb_strtoupper(mb_substr($e['prenom'], 0, 1) . mb_substr($e['nom'], 0, 1)) ?>
                        </span>
                        <?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?>
                    </td>

                    <td class="etu-td-email">
                        <a href="mailto:<?= htmlspecialchars($e['email']) ?>">
                            <?= htmlspecialchars($e['email']) ?>
                        </a>
                    </td>

                    <td>
                        <span class="etu-badge-niveau">
                            <?= htmlspecialchars($e['niveau'] ?? '—') ?>
                        </span>
                    </td>

                    <td><?= htmlspecialchars((string)($e['annee'] ?? '—')) ?></td>

                    <td class="etu-td-actions">

                        <!-- Bouton modifier -->
                        <button
                            type="button"
                            class="etu-btn-icon etu-btn-edit"
                            title="Modifier"
                            onclick="ouvrirModal('modal-edit-<?= $e['id'] ?>')"
                        >
                            <i class="ti ti-pencil"></i>
                        </button>

                        <!-- Bouton supprimer -->
                        <button
                            type="button"
                            class="etu-btn-icon etu-btn-delete"
                            title="Supprimer"
                            onclick="ouvrirModal('modal-delete-<?= $e['id'] ?>')"
                        >
                            <i class="ti ti-trash"></i>
                        </button>

                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- MODALS EDIT + DELETE par étudiant -->
    <?php foreach ($etudiants as $e): ?>

    <!-- ─── MODAL MODIFIER ─────────────────────────────────────────── -->
    <div
        id="modal-edit-<?= $e['id'] ?>"
        class="etu-modal-backdrop"
        onclick="fermerModalBackdrop(event, 'modal-edit-<?= $e['id'] ?>')"
    >
        <div class="etu-modal">

            <div class="etu-modal-header">
                <span class="etu-modal-title">
                    <i class="ti ti-pencil"></i>
                    Modifier l'étudiant
                </span>
                <button
                    type="button"
                    class="etu-modal-close"
                    onclick="fermerModal('modal-edit-<?= $e['id'] ?>')"
                >
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form
                method="POST"
                action="/?page=etu_manage_update"
                class="etu-modal-form"
            >
                <input type="hidden" name="id"      value="<?= $e['id'] ?>">
                <input type="hidden" name="filiere_redirect" value="<?= htmlspecialchars($filiere) ?>">
                <input type="hidden" name="classe_redirect"  value="<?= htmlspecialchars($classe) ?>">

                <div class="etu-form-grid">

                    <div class="etu-form-group">
                        <label class="etu-form-label">CIN</label>
                        <input
                            type="number"
                            class="etu-form-input"
                            name="cin"
                            value="<?= htmlspecialchars((string)($e['cin'] ?? '')) ?>"
                            placeholder="12345678"
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Prénom <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="prenom"
                            value="<?= htmlspecialchars($e['prenom']) ?>"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Nom <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="nom"
                            value="<?= htmlspecialchars($e['nom']) ?>"
                            required
                        >
                    </div>

                    <div class="etu-form-group etu-form-group--full">
                        <label class="etu-form-label">Email <span class="req">*</span></label>
                        <input
                            type="email"
                            class="etu-form-input"
                            name="email"
                            value="<?= htmlspecialchars($e['email']) ?>"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Filière <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="filiere"
                            value="<?= htmlspecialchars($e['filiere'] ?? $filiere) ?>"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Classe <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="classe"
                            value="<?= htmlspecialchars($e['classe'] ?? $classe) ?>"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Niveau <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="niveau"
                            value="<?= htmlspecialchars($e['niveau'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Année</label>
                        <input
                            type="number"
                            class="etu-form-input"
                            name="annee"
                            value="<?= htmlspecialchars((string)($e['annee'] ?? date('Y'))) ?>"
                            min="2000"
                            max="2099"
                        >
                    </div>

                </div>

                <div class="etu-modal-actions">
                    <button type="submit" class="etu-btn-primary">
                        <i class="ti ti-device-floppy"></i>
                        Enregistrer
                    </button>
                    <button
                        type="button"
                        class="etu-btn-secondary"
                        onclick="fermerModal('modal-edit-<?= $e['id'] ?>')"
                    >
                        Annuler
                    </button>
                </div>

            </form>

        </div>
    </div>

    <!-- ─── MODAL SUPPRIMER ────────────────────────────────────────── -->
    <div
        id="modal-delete-<?= $e['id'] ?>"
        class="etu-modal-backdrop"
        onclick="fermerModalBackdrop(event, 'modal-delete-<?= $e['id'] ?>')"
    >
        <div class="etu-modal etu-modal--danger">

            <div class="etu-modal-header">
                <span class="etu-modal-title etu-modal-title--danger">
                    <i class="ti ti-alert-triangle"></i>
                    Supprimer l'étudiant
                </span>
                <button
                    type="button"
                    class="etu-modal-close"
                    onclick="fermerModal('modal-delete-<?= $e['id'] ?>')"
                >
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <div class="etu-modal-body">
                <p class="etu-delete-msg">
                    Êtes-vous sûr de vouloir supprimer
                    <strong><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></strong> ?
                    <br>
                    <span class="etu-delete-warn">
                        Cette action est irréversible et supprimera toutes ses données associées.
                    </span>
                </p>
            </div>

            <form
                method="POST"
                action="/?page=etu_manage_delete"
            >
                <input type="hidden" name="id"              value="<?= $e['id'] ?>">
                <input type="hidden" name="filiere"         value="<?= htmlspecialchars($filiere) ?>">
                <input type="hidden" name="classe"          value="<?= htmlspecialchars($classe) ?>">

                <div class="etu-modal-actions">
                    <button
                        type="submit"
                        class="etu-btn-danger"
                    >
                        <i class="ti ti-trash"></i>
                        Confirmer la suppression
                    </button>
                    <button
                        type="button"
                        class="etu-btn-secondary"
                        onclick="fermerModal('modal-delete-<?= $e['id'] ?>')"
                    >
                        Annuler
                    </button>
                </div>

            </form>

        </div>
    </div>

    <?php endforeach; ?>

    <?php else: ?>

    <!-- Classe vide -->
    <div class="etu-empty">
        <i class="ti ti-user-off"></i>
        <p>Aucun étudiant dans cette classe.</p>
    </div>

    <?php endif; ?>

    <!-- ══ BOUTON AJOUTER ═════════════════════════════════════════════════ -->
    <button
        type="button"
        class="etu-btn-add"
        onclick="ouvrirModal('modal-ajouter')"
        title="Ajouter un étudiant"
    >
        <i class="ti ti-plus"></i>
        Ajouter un étudiant
    </button>

    <!-- ══ MODAL AJOUTER ══════════════════════════════════════════════════ -->
    <div
        id="modal-ajouter"
        class="etu-modal-backdrop"
        onclick="fermerModalBackdrop(event, 'modal-ajouter')"
    >
        <div class="etu-modal">

            <div class="etu-modal-header">
                <span class="etu-modal-title">
                    <i class="ti ti-user-plus"></i>
                    Nouvel étudiant
                </span>
                <button
                    type="button"
                    class="etu-modal-close"
                    onclick="fermerModal('modal-ajouter')"
                >
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <form
                method="POST"
                action="/?page=etu_manage_save"
                class="etu-modal-form"
            >
                <div class="etu-form-grid">

                    <div class="etu-form-group">
                        <label class="etu-form-label">CIN</label>
                        <input
                            type="number"
                            class="etu-form-input"
                            name="cin"
                            placeholder="12345678"
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Prénom <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="prenom"
                            placeholder="Ahmed"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Nom <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="nom"
                            placeholder="Ben Salah"
                            required
                        >
                    </div>

                    <div class="etu-form-group etu-form-group--full">
                        <label class="etu-form-label">Email <span class="req">*</span></label>
                        <input
                            type="email"
                            class="etu-form-input"
                            name="email"
                            placeholder="ahmed.bensalah@etudiant.insat.tn"
                            required
                        >
                    </div>

                    <div class="etu-form-group etu-form-group--full">
                        <label class="etu-form-label">Mot de passe initial</label>
                        <input
                            type="password"
                            class="etu-form-input"
                            name="mot_passe"
                            placeholder="Laissez vide pour 'changeme123'"
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Filière <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="filiere"
                            value="<?= htmlspecialchars($filiere) ?>"
                            placeholder="GL"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Classe <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="classe"
                            value="<?= htmlspecialchars($classe) ?>"
                            placeholder="GL2-1"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Niveau <span class="req">*</span></label>
                        <input
                            type="text"
                            class="etu-form-input"
                            name="niveau"
                            placeholder="2"
                            required
                        >
                    </div>

                    <div class="etu-form-group">
                        <label class="etu-form-label">Année</label>
                        <input
                            type="number"
                            class="etu-form-input"
                            name="annee"
                            value="<?= date('Y') ?>"
                            min="2000"
                            max="2099"
                        >
                    </div>

                </div>

                <div class="etu-modal-actions">
                    <button type="submit" class="etu-btn-primary">
                        <i class="ti ti-user-plus"></i>
                        Ajouter
                    </button>
                    <button
                        type="button"
                        class="etu-btn-secondary"
                        onclick="fermerModal('modal-ajouter')"
                    >
                        Annuler
                    </button>
                </div>

            </form>

        </div>
    </div>

    <?php else: ?>

    <!-- ══ ÉTAT INITIAL (aucun filtre) ════════════════════════════════════ -->
    <div class="etu-welcome">
        <div class="etu-welcome-icon">
            <i class="ti ti-filter"></i>
        </div>
        <p class="etu-welcome-msg">
            Sélectionnez une filière et une classe pour afficher les étudiants.
        </p>
    </div>

    <?php endif; ?>

</div>

<!-- Données PHP → JS (injection JSON uniquement, pas de logique) -->
<script>
const CLASSES_BY_FILIERE = <?= json_encode($classesByFiliere, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="/js/admin-modals.js"></script>
<script src="/js/admin-etudiants.js"></script>
