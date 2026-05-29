<?php require_once BASE_PATH . '/views/layouts/header.php'; ?>
<link rel="stylesheet" href="/css/groupes.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="grp-page">

    <!-- Flash -->
    <?php if ($flash): ?>
    <div class="flash flash--<?= $flash['type'] ?>">
        <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="grp-header">
        <div class="grp-header-icon"><i class="ti ti-users"></i></div>
        <div>
            <h1 class="grp-header-title">Gestion des Groupes</h1>
            <p class="grp-header-sub">Créez des groupes, assignez des modérateurs et gérez les membres.</p>
        </div>
    </div>

    <!-- Grille formulaires -->
    <div class="grp-grid">

        <!-- Créer un groupe -->
        <div class="grp-card">
            <div class="grp-card-header grp-card-header--blue">
                <i class="ti ti-circle-plus"></i>
                <span>Créer un nouveau groupe</span>
            </div>
            <form method="POST" action="/?page=groupe_create" class="grp-form">
                <div class="grp-form-group">
                    <label class="grp-label">Nom du groupe <span class="req">*</span></label>
                    <input type="text" name="new_group_name" class="grp-input"
                           placeholder="ex: Club Web, Classe GL3..." required>
                </div>
                <div class="grp-form-group">
                    <label class="grp-label">
                        Email du modérateur
                        <span class="grp-hint">(Optionnel)</span>
                    </label>
                    <input type="email" name="mod_email" class="grp-input"
                           placeholder="ex: prof@insat.u-carthage.tn">
                </div>
                <div class="grp-form-submit">
                    <button type="submit" class="grp-btn grp-btn--blue">
                        <i class="ti ti-plus"></i> Créer le groupe
                    </button>
                </div>
            </form>
        </div>

        <!-- Assigner un membre -->
        <div class="grp-card">
            <div class="grp-card-header grp-card-header--green">
                <i class="ti ti-user-plus"></i>
                <span>Assigner un membre</span>
            </div>
            <form method="POST" action="/?page=groupe_add_member" class="grp-form">
                <div class="grp-form-group">
                    <label class="grp-label">Email de l'utilisateur <span class="req">*</span></label>
                    <input type="email" name="student_email" class="grp-input"
                           placeholder="ex: etudiant@insat.u-carthage.tn" required>
                </div>
                <div class="grp-form-group">
                    <label class="grp-label">Groupe <span class="req">*</span></label>
                    <div class="grp-select-wrap">
                        <select name="group_name" class="grp-input" required>
                            <option value="">— Choisir un groupe —</option>
                            <?php foreach ($allGroups as $gName): ?>
                                <option value="<?= htmlspecialchars($gName) ?>">
                                    <?= htmlspecialchars($gName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ti ti-chevron-down grp-select-icon"></i>
                    </div>
                </div>
                <div class="grp-form-submit">
                    <button type="submit" class="grp-btn grp-btn--green">
                        <i class="ti ti-check"></i> Assigner au groupe
                    </button>
                </div>
            </form>
        </div>

        <!-- Retirer un membre -->
        <div class="grp-card">
            <div class="grp-card-header grp-card-header--red">
                <i class="ti ti-user-minus"></i>
                <span>Retirer un membre</span>
            </div>
            <form method="POST" action="/?page=groupe_remove_member" class="grp-form">
                <div class="grp-form-group">
                    <label class="grp-label">Email de l'utilisateur <span class="req">*</span></label>
                    <input type="email" name="student_email" class="grp-input"
                           placeholder="ex: etudiant@insat.u-carthage.tn" required>
                </div>
                <div class="grp-form-group">
                    <label class="grp-label">Groupe <span class="req">*</span></label>
                    <div class="grp-select-wrap">
                        <select name="group_name" class="grp-input" required>
                            <option value="">— Choisir un groupe —</option>
                            <?php foreach ($allGroups as $gName): ?>
                                <option value="<?= htmlspecialchars($gName) ?>">
                                    <?= htmlspecialchars($gName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i class="ti ti-chevron-down grp-select-icon"></i>
                    </div>
                </div>
                <div class="grp-form-submit">
                    <button type="submit" class="grp-btn grp-btn--red">
                        <i class="ti ti-x"></i> Retirer du groupe
                    </button>
                </div>
            </form>
        </div>

    </div>

    <!-- Liste des groupes existants -->
    <div class="grp-card grp-list-card">
        <div class="grp-card-header grp-card-header--gray">
            <i class="ti ti-layout-list"></i>
            <span>Groupes existants</span>
            <span class="grp-count-badge"><?= count($allGroups) ?></span>
        </div>

        <?php if (empty($allGroups)): ?>
            <p class="grp-empty">Aucun groupe n'a été créé pour le moment.</p>
        <?php else: ?>
            <div class="grp-chips">
                <?php foreach ($allGroups as $gName): ?>
                    <span class="grp-chip">
                        <i class="ti ti-users-group"></i>
                        <?= htmlspecialchars($gName) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>
