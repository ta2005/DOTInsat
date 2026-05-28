<?php
// views/pages/admin/enseignants.php
// Variables : $profs (array), $search (string), $flash (array|null), $config (array)
?>
<link rel="stylesheet" href="/css/etudiants.css">
<link rel="stylesheet" href="/css/enseignants.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="etu-page">

    <!-- ══ FLASH ══════════════════════════════════════════════════════════ -->
    <?php if ($flash): ?>
    <div class="flash flash--<?= $flash['type'] ?>">
        <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- ══ EN-TÊTE ════════════════════════════════════════════════════════ -->
    <div class="etu-header">
        <div class="etu-header-icon">
            <i class="ti ti-school"></i>
        </div>
        <div>
            <h1 class="etu-header-title">Gestion des Enseignants</h1>
            <p class="etu-header-sub">Recherchez, modifiez et supprimez les enseignants</p>
        </div>
    </div>

    <!-- ══ BARRE DE RECHERCHE ═════════════════════════════════════════════ -->
    <div class="ens-search-wrap">
        <i class="ti ti-search ens-search-icon"></i>
        <input
            type="text"
            id="ens-search-input"
            class="ens-search-input"
            placeholder="Rechercher par nom, prénom ou email…"
            value="<?= htmlspecialchars($search) ?>"
            autocomplete="off"
        >
        <?php if (!empty($search)): ?>
        <a href="/?page=ens_manage" class="ens-search-clear" title="Effacer">
            <i class="ti ti-x"></i>
        </a>
        <?php endif; ?>
    </div>

    <!-- ══ BARRE RÉSULTATS ════════════════════════════════════════════════ -->
    <div class="etu-list-header">
        <div class="etu-list-meta">
            <span id="ens-count" class="etu-count">
                <?php $n = count($profs); ?>
                <?= $n ?> enseignant<?= $n !== 1 ? 's' : '' ?>
                <?= !empty($search) ? ' · « ' . htmlspecialchars($search) . ' »' : '' ?>
            </span>
        </div>
        <button type="button" class="etu-btn-add ens-btn-add-top" onclick="ouvrirModal('modal-ajouter')">
            <i class="ti ti-plus"></i> Ajouter un enseignant
        </button>
    </div>

    <!-- ══ TABLE ══════════════════════════════════════════════════════════ -->
    <?php if (empty($profs)): ?>
    <div class="etu-empty">
        <i class="ti ti-user-off"></i>
        <p>Aucun enseignant trouvé<?= !empty($search) ? ' pour cette recherche' : '' ?>.</p>
    </div>
    <?php else: ?>

    <div class="etu-table-wrap">
        <table class="etu-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>CIN</th>
                    <th>Nom complet</th>
                    <th>Email</th>
                    <th>Classes &amp; Matières</th>
                    <th class="etu-th-actions">Actions</th>
                </tr>
            </thead>
            <tbody id="ens-tbody">
                <?php foreach ($profs as $i => $prof):
                    $initiales   = mb_strtoupper(mb_substr($prof['prenom'], 0, 1) . mb_substr($prof['nom'], 0, 1));
                    $enseignements = $prof['enseignements'] ?? [];
                    $parClasse   = [];
                    foreach ($enseignements as $e) {
                        $parClasse[$e['classe'] ?? '—'][] = $e['matiere'] ?? '—';
                    }
                ?>
                <tr class="etu-row ens-row"
                    id="ens-row-<?= $prof['id'] ?>"
                    data-search="<?= htmlspecialchars(strtolower($prof['nom'].' '.$prof['prenom'].' '.$prof['email'])) ?>">

                    <td class="etu-td-num"><?= $i + 1 ?></td>

                    <td class="etu-td-cin">
                        <?= $prof['cin'] ? htmlspecialchars((string)$prof['cin']) : '<span class="etu-empty">—</span>' ?>
                    </td>

                    <td class="etu-td-name">
                        <span class="etu-avatar ens-avatar">
                            <?= $initiales ?>
                        </span>
                        <div class="ens-name-block">
                            <span class="ens-fullname"><?= htmlspecialchars($prof['prenom'].' '.$prof['nom']) ?></span>
                        </div>
                    </td>

                    <td class="etu-td-email">
                        <a href="mailto:<?= htmlspecialchars($prof['email']) ?>">
                            <?= htmlspecialchars($prof['email']) ?>
                        </a>
                    </td>

                    <td class="ens-td-tags">
                        <?php if (empty($parClasse)): ?>
                            <span class="ens-tag ens-tag--empty">Aucun enseignement</span>
                        <?php else: ?>
                            <?php foreach ($parClasse as $classe => $matieres): ?>
                            <span class="ens-tag">
                                <span class="ens-tag-classe"><?= htmlspecialchars($classe) ?></span>
                                <span class="ens-tag-sep">·</span>
                                <span class="ens-tag-matieres"><?= htmlspecialchars(implode(', ', $matieres)) ?></span>
                            </span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>

                    <td class="etu-td-actions">
                        <button
                            type="button"
                            class="etu-btn-icon etu-btn-edit"
                            title="Modifier"
                            onclick="ouvrirModal('modal-edit-<?= $prof['id'] ?>')"
                        ><i class="ti ti-pencil"></i></button>

                        <button
                            type="button"
                            class="etu-btn-icon etu-btn-delete"
                            title="Supprimer"
                            onclick="ouvrirModal('modal-delete-<?= $prof['id'] ?>')"
                        ><i class="ti ti-trash"></i></button>
                    </td>

                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ══ MODALS EDIT + DELETE ══════════════════════════════════════════ -->
    <?php foreach ($profs as $prof): ?>

    <!-- ─── MODAL MODIFIER ──────────────────────────────────────────── -->
    <div id="modal-edit-<?= $prof['id'] ?>"
         class="etu-modal-backdrop"
         onclick="fermerModalBackdrop(event,'modal-edit-<?= $prof['id'] ?>')">
        <div class="etu-modal">
            <div class="etu-modal-header">
                <span class="etu-modal-title">
                    <i class="ti ti-pencil"></i> Modifier l'enseignant
                </span>
                <button type="button" class="etu-modal-close"
                        onclick="fermerModal('modal-edit-<?= $prof['id'] ?>')">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form method="POST" action="/?page=ens_manage_update" class="etu-modal-form">
                <input type="hidden" name="id" value="<?= $prof['id'] ?>">
                <div class="etu-form-grid">
                    <div class="etu-form-group">
                        <label class="etu-form-label">CIN</label>
                        <input type="number" class="etu-form-input" name="cin"
                               value="<?= htmlspecialchars((string)($prof['cin'] ?? '')) ?>"
                               placeholder="12345678">
                    </div>
                    <div class="etu-form-group">
                        <label class="etu-form-label">Prénom <span class="req">*</span></label>
                        <input type="text" class="etu-form-input" name="prenom"
                               value="<?= htmlspecialchars($prof['prenom']) ?>" required>
                    </div>
                    <div class="etu-form-group">
                        <label class="etu-form-label">Nom <span class="req">*</span></label>
                        <input type="text" class="etu-form-input" name="nom"
                               value="<?= htmlspecialchars($prof['nom']) ?>" required>
                    </div>
                    <div class="etu-form-group etu-form-group--full">
                        <label class="etu-form-label">Email <span class="req">*</span></label>
                        <input type="email" class="etu-form-input" name="email"
                               value="<?= htmlspecialchars($prof['email']) ?>" required>
                    </div>
                </div>
                <div class="etu-modal-actions">
                    <button type="submit" class="etu-btn-primary">
                        <i class="ti ti-device-floppy"></i> Enregistrer
                    </button>
                    <button type="button" class="etu-btn-secondary"
                            onclick="fermerModal('modal-edit-<?= $prof['id'] ?>')">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ─── MODAL SUPPRIMER ─────────────────────────────────────────── -->
    <div id="modal-delete-<?= $prof['id'] ?>"
         class="etu-modal-backdrop"
         onclick="fermerModalBackdrop(event,'modal-delete-<?= $prof['id'] ?>')">
        <div class="etu-modal etu-modal--danger">
            <div class="etu-modal-header">
                <span class="etu-modal-title etu-modal-title--danger">
                    <i class="ti ti-alert-triangle"></i> Supprimer l'enseignant
                </span>
                <button type="button" class="etu-modal-close"
                        onclick="fermerModal('modal-delete-<?= $prof['id'] ?>')">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <div class="etu-modal-body">
                <p class="etu-delete-msg">
                    Êtes-vous sûr de vouloir supprimer
                    <strong><?= htmlspecialchars($prof['prenom'].' '.$prof['nom']) ?></strong> ?
                    <br>
                    <span class="etu-delete-warn">
                        Cette action est irréversible et supprimera toutes ses données associées.
                    </span>
                </p>
            </div>
            <form method="POST" action="/?page=ens_manage_delete">
                <input type="hidden" name="id" value="<?= $prof['id'] ?>">
                <div class="etu-modal-actions">
                    <button type="submit" class="etu-btn-danger">
                        <i class="ti ti-trash"></i> Confirmer la suppression
                    </button>
                    <button type="button" class="etu-btn-secondary"
                            onclick="fermerModal('modal-delete-<?= $prof['id'] ?>')">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php endforeach; ?>

    <?php endif; ?>

    <!-- ══ MODAL AJOUTER ══════════════════════════════════════════════════ -->
    <div id="modal-ajouter"
         class="etu-modal-backdrop"
         onclick="fermerModalBackdrop(event,'modal-ajouter')">
        <div class="etu-modal">
            <div class="etu-modal-header">
                <span class="etu-modal-title">
                    <i class="ti ti-user-plus"></i> Nouvel enseignant
                </span>
                <button type="button" class="etu-modal-close" onclick="fermerModal('modal-ajouter')">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form method="POST" action="/?page=ens_manage_save" class="etu-modal-form">
                <div class="etu-form-grid">
                    <div class="etu-form-group">
                        <label class="etu-form-label">CIN</label>
                        <input type="number" class="etu-form-input" name="cin" placeholder="12345678">
                    </div>
                    <div class="etu-form-group">
                        <label class="etu-form-label">Prénom <span class="req">*</span></label>
                        <input type="text" class="etu-form-input" name="prenom" placeholder="Mohamed" required>
                    </div>
                    <div class="etu-form-group">
                        <label class="etu-form-label">Nom <span class="req">*</span></label>
                        <input type="text" class="etu-form-input" name="nom" placeholder="Ben Ali" required>
                    </div>
                    <div class="etu-form-group etu-form-group--full">
                        <label class="etu-form-label">Email <span class="req">*</span></label>
                        <input type="email" class="etu-form-input" name="email"
                               placeholder="prof@insat.tn" required>
                    </div>
                    <div class="etu-form-group etu-form-group--full">
                        <label class="etu-form-label">Mot de passe initial</label>
                        <input type="password" class="etu-form-input" name="mot_passe"
                               placeholder="Laissez vide pour 'changeme123'">
                    </div>
                </div>
                <div class="etu-modal-actions">
                    <button type="submit" class="etu-btn-primary">
                        <i class="ti ti-user-plus"></i> Ajouter
                    </button>
                    <button type="button" class="etu-btn-secondary" onclick="fermerModal('modal-ajouter')">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- /.etu-page -->

<script>
/* ── Recherche dynamique ── */
(function () {
    const input = document.getElementById('ens-search-input');
    const rows  = document.querySelectorAll('.ens-row');
    const count = document.getElementById('ens-count');
    if (!input) return;

    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = this.value.toLowerCase().trim();
            let n = 0;
            rows.forEach(row => {
                const match = !q || (row.dataset.search || '').includes(q);
                row.style.display = match ? '' : 'none';
                if (match) n++;
            });
            count.textContent = n + ' enseignant' + (n !== 1 ? 's' : '')
                + (q ? ' · « ' + this.value + ' »' : '');
        }, 180);
    });
})();

/* ── Modals (même API que etudiants.php) ── */
function ouvrirModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add('etu-modal-backdrop--open');
    document.body.style.overflow = 'hidden';
}
function fermerModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('etu-modal-backdrop--open');
    document.body.style.overflow = '';
}
function fermerModalBackdrop(event, id) {
    if (event.target === event.currentTarget) fermerModal(id);
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.etu-modal-backdrop--open').forEach(m => {
            m.classList.remove('etu-modal-backdrop--open');
            document.body.style.overflow = '';
        });
    }
});
</script>
