<?php
require_once __DIR__ . '/../../layouts/header.php';

$flash         = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Le professeur ne voit que les réclamations que l'admin lui a transmises
$a_traiter = array_values(array_filter($reclamations, fn($r) => $r['statut'] === 'ACCEPTEE_PAR_ADMINISTRATEUR'));

$traitees = array_values(array_filter($reclamations, fn($r) =>
    in_array($r['statut'], ['ACCEPTEE_PAR_LE_PROFESSEUR', 'REFUSEE_PAR_LE_PROFESSEUR'])
));

$statut_labels = [
    'ACCEPTEE_PAR_LE_PROFESSEUR' => [
        'label' => 'Note modifiée',
        'class' => 'badge--green'
    ],

    'REFUSEE_PAR_LE_PROFESSEUR' => [
        'label' => 'Refusée',
        'class' => 'badge--red'
    ],
];
?>



<div class="wrap">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<main>

    <!-- FLASH -->
    <?php if ($flash): ?>
    <div class="flash flash--<?= $flash['type'] ?>">
        <i class="ti <?= $flash['type'] === 'success'
            ? 'ti-circle-check'
            : 'ti-circle-x' ?>"></i>

        <?= htmlspecialchars($flash['msg']) ?>
    </div>
    <?php endif; ?>

    <!-- NOTIFICATIONS -->
    <?php if (!empty($notifications)): ?>
    <div class="notif-section">

        <div class="notif-section-title">
            <i class="ti ti-bell"></i>
            Notifications

            <span class="notif-badge">
                <?= count($notifications) ?>
            </span>
        </div>

        <?php foreach ($notifications as $n): ?>
        <div class="notif-item <?= $n['lu'] ? 'notif-item--lu' : '' ?>">

            <i class="ti ti-info-circle notif-icon"></i>

            <div class="notif-content">

                <p class="notif-msg">
                    <?= htmlspecialchars($n['message']) ?>
                </p>

                <span class="notif-date">
                    <?= $n['date'] ?>
                    · expire <?= $n['expiration'] ?>
                </span>

            </div>
        </div>
        <?php endforeach; ?>

    </div>
    <?php endif; ?>

    <!-- HEADER PAGE -->
    <div class="form-page-header">

        <div class="form-page-icon">
            <i class="ti ti-message-report"></i>
        </div>

        <div>
            <h2 class="form-page-title">
                Réclamations à traiter
            </h2>

            <p class="form-page-sub">
                <?= count($a_traiter) ?>
                demande(s) transmise(s) par l'administration
            </p>
        </div>

    </div>

    <!-- RECLAMATIONS A TRAITER -->
    <?php foreach ($a_traiter as $r): ?>

    <div class="card recl-card" id="recl-<?= $r['id'] ?>">

        <div class="recl-header">

            <div class="recl-meta">

                <span class="recl-student">
                    <?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?>
                </span>

                <span class="recl-num">
                    <?= htmlspecialchars($r['num']) ?>
                </span>

            </div>

            <span class="badge badge--blue">
                Transmise par admin
            </span>

        </div>

        <div class="recl-body">

            <div class="recl-info-row">
                <span class="recl-label">Matière</span>

                <span class="recl-val">
                    <?= htmlspecialchars($r['matiere_nom']) ?>
                </span>
            </div>

            <div class="recl-info-row">
                <span class="recl-label">Type</span>

                <span class="recl-val">
                    <?= htmlspecialchars($r['type_eval_label']) ?>
                </span>
            </div>

            <div class="recl-info-row">
                <span class="recl-label">Note actuelle</span>

                <span class="recl-val">
                    <?= $r['note_actuelle'] ?>/20
                </span>
            </div>

            <div class="recl-info-row recl-info-row--full">
                <span class="recl-label">
                    Motif de l'étudiant
                </span>

                <span class="recl-val">
                    <?= htmlspecialchars($r['commentaire']) ?>
                </span>
            </div>

            <span class="recl-date">
                <?= $r['date_soumission'] ?>
            </span>

        </div>

        <!-- ACTIONS -->
        <div class="recl-actions">

            <button
                type="button"
                class="form-btn-primary"
                onclick="ouvrirAccepter(<?= $r['id'] ?>)"
            >
                <i class="ti ti-check"></i>
                Accepter &amp; Modifier la note
            </button>

            <button
                type="button"
                class="form-btn-secondary form-btn-secondary--red"
                onclick="ouvrirRefuser(<?= $r['id'] ?>)"
            >
                <i class="ti ti-x"></i>
                Refuser
            </button>

        </div>

        <!-- PANEL ACCEPTER -->
        <div
            id="panel-accepter-<?= $r['id'] ?>"
            class="recl-panel recl-panel--green"
            style="display:none;"
        >

            <form
                method="POST"
                action="/?page=prof-reclamation-action"
            >

                <input
                    type="hidden"
                    name="action"
                    value="prof_approuver"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $r['id'] ?>"
                >

                <div class="panel-inner">

                    <div class="form-group">

                        <label class="form-label">
                            <i class="ti ti-edit"></i>
                            Nouvelle note (sur 20)
                        </label>

                        <input
                            type="number"
                            class="form-input"
                            name="nouvelle_note"
                            min="0"
                            max="20"
                            step="0.5"
                            value="<?= $r['note_actuelle'] ?>"
                            placeholder="Ex: 14.5"
                            required
                        >

                    </div>

                    <div class="panel-btns">

                        <button
                            type="submit"
                            class="form-btn-primary"
                        >
                            <i class="ti ti-device-floppy"></i>
                            Confirmer la nouvelle note
                        </button>

                        <button
                            type="button"
                            class="form-btn-secondary"
                            onclick="fermerPanels(<?= $r['id'] ?>)"
                        >
                            Annuler
                        </button>

                    </div>

                </div>

            </form>

        </div>

        <!-- PANEL REFUSER -->
        <div
            id="panel-refuser-<?= $r['id'] ?>"
            class="recl-panel recl-panel--red"
            style="display:none;"
        >

            <form
                method="POST"
                action="/?page=prof-reclamation-action"
            >

                <input
                    type="hidden"
                    name="action"
                    value="prof_refuser"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= $r['id'] ?>"
                >

                <div class="panel-inner">

                    <div class="form-group">

                        <label class="form-label">
                            <i class="ti ti-message-x"></i>
                            Raison du refus (obligatoire)
                        </label>

                        <textarea
                            class="form-textarea"
                            name="raison"
                            rows="3"
                            placeholder="Expliquez pourquoi la réclamation est refusée..."
                            required
                        ></textarea>

                    </div>

                    <div class="panel-btns">

                        <button
                            type="submit"
                            class="form-btn-secondary form-btn-secondary--red"
                        >
                            <i class="ti ti-send"></i>
                            Confirmer le refus
                        </button>

                        <button
                            type="button"
                            class="form-btn-secondary"
                            onclick="fermerPanels(<?= $r['id'] ?>)"
                        >
                            Annuler
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    <?php endforeach; ?>

    <!-- EMPTY -->
    <?php if (empty($a_traiter)): ?>
    <div class="empty-state">

        <i class="ti ti-circle-check"></i>

        <p>
            Aucune réclamation en attente.
        </p>

    </div>
    <?php endif; ?>

    <!-- HISTORIQUE -->
    <?php if (!empty($traitees)): ?>

    <div
        class="recl-section-title recl-section-title--muted"
        style="margin-top:32px;"
    >
        <i class="ti ti-archive"></i>
        Réclamations traitées
    </div>

    <?php foreach ($traitees as $r): ?>

    <div class="card recl-card recl-card--muted">

        <div class="recl-header">

            <div class="recl-meta">

                <span class="recl-student">
                    <?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?>
                </span>

                <span class="recl-num">
                    <?= htmlspecialchars($r['num']) ?>
                </span>

            </div>

            <span class="badge <?= $statut_labels[$r['statut']]['class'] ?>">
                <?= $statut_labels[$r['statut']]['label'] ?>
            </span>

        </div>

        <div class="recl-body">

            <div class="recl-info-row">

                <span class="recl-label">
                    Matière
                </span>

                <span class="recl-val">
                    <?= htmlspecialchars($r['matiere_nom']) ?>
                    —
                    <?= htmlspecialchars($r['type_eval_label']) ?>
                </span>

            </div>

            <?php if (!empty($r['note_nouvelle'])): ?>

            <div class="recl-info-row">

                <span class="recl-label">
                    Note
                </span>

                <span class="recl-val">
                    <?= $r['note_actuelle'] ?>/20
                    →
                    <strong style="color:#4ade80">
                        <?= $r['note_nouvelle'] ?>/20
                    </strong>
                </span>

            </div>

            <?php endif; ?>

            <?php if (!empty($r['raison_refus'])): ?>

            <div class="recl-info-row">

                <span class="recl-label">
                    Raison
                </span>

                <span class="recl-val">
                    <?= htmlspecialchars($r['raison_refus']) ?>
                </span>

            </div>

            <?php endif; ?>

            <span class="recl-date">
                <?= $r['date_soumission'] ?>
            </span>

        </div>

    </div>

    <?php endforeach; ?>
    <?php endif; ?>

</main>
</div>



<script src="/js/prof-reclamations.js"></script>