<?php require_once BASE_PATH . '/views/layouts/header.php';?>

<div class="wrap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
<main>

<?php
$etudiant_id   = 'etudiant_' . ($_SESSION['user_id'] ?? 1);
$notifications = []; 
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);


// lehne bch ychouf les matiere w les prof illi mawjoudin fi
$notesJS = [];
$profsJS = [];
foreach ($matieres as $m) {
    // naabi mll bd aala hassb ll type d'evaluation ll mawjouda fl matiere (matiere par matiere)
    // nhot controle_id m3a note bch JS yba3tou fll hidden input
    foreach ($m['types'] as $t) {
        $notesJS[$m['id']][$t['type']] = [
            'note'        => $t['note'],
            'controle_id' => $t['controle_id'],
        ];
    }
    $profsJS[$m['id']] = $m['prof'];
}
?>

<!-- message flash -->
<?php if ($flash): ?>
<div class="flash flash--<?= $flash['type'] ?>">
    <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
    <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

 <!-- mtaa notif attetionfassakhha ken ma khlatech tkamalha -->
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
            <span class="notif-date"><?= $n['date'] ?><?= !empty($n['expiration']) ? ' · expire ' . $n['expiration'] : '' ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

 <!-- ll faza illi mel fouk -->
<div class="form-page-header">
    <div class="form-page-icon form-page-icon--red">
        <i class="ti ti-message-report"></i>
    </div>
    <div>
        <h2 class="form-page-title">Nouvelle Réclamation</h2>
        <p class="form-page-sub">Signalez un problème concernant une note</p>
    </div>
</div>

<form class="card form-card" method="POST" action="/?page=save-reclamation">

    
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-user"></i> Informations personnelles
        </div>
        <!-- nom w prenom ma yekhdmouch fll store(), etudiant_id yiji mll session -->
        <!--les matieres yijou mll session directement -->
    </div>

    <!-- mtaa matiere w type d'evaluation -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-book"></i> Matière concernée
        </div>
        <!-- select matiere visible directement, matieres yijou mll session -->
        <div id="matiere-block" class="form-group">
            <label class="form-label">Sélectionner la matière</label>
            <?php $firstMatiere = $matieres[0] ?? null; ?>
            <select class="form-select" name="matiere" id="matiere-select"
            onchange="onMatiereChange(this)" required>
                <?php foreach ($matieres as $m): ?>
                <option value="<?= $m['id'] ?>" <?= ($firstMatiere && $m['id'] === $firstMatiere['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($m['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>


            <!-- type d'evaluation yebda baad ma nselecti matiere -->
        <div id="eval-block" class="form-group" style="display:none;">
            <label class="form-label">Type d'évaluation</label>
            <select class="form-select" id="rec-eval" name="type_eval" onchange="onEvalChange()">
                <option value="" disabled selected>Choisir</option>
                <!-- lahne js yedakhel -->
            </select>
        </div>

        <!-- hidden input bch nba3th controle_id lll store() -->
        <input type="hidden" name="controle_id" id="controle-id-input" value="">


          <!-- note w prof -->
        <div id="note-info-block" class="note-info-card" style="display:none;">
            <div class="note-info-row">
                <div class="note-info-item">
                    <span class="note-info-label">Note obtenue</span>
                    <span class="note-info-val" id="note-val">—</span>
                </div>
                
                <div class="note-info-divider"></div>
                <div class="note-info-item">
                    <span class="note-info-label">Enseignant</span>
                    <span class="note-info-val" id="prof-val">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- text mta3 commentaire -->
    <div class="form-section">
        <div class="form-section-label">
            <i class="ti ti-message"></i> Commentaire
        </div>
        <textarea class="form-textarea" name="commentaire" rows="4" required></textarea>
    </div>

    <!-- les boutons -->
    <div class="form-actions">
        <a href="/?page=home" class="form-btn-secondary">Annuler</a>
        <button type="submit" class="form-btn-primary form-btn-primary--red">
            Envoyer
        </button>
    </div>

</form>


    
<?php
//section mtaa reclamations illi taala t7ot fiha les reclamations illi 3mlhom l'etudiant deja
// bch ychouf chnowa 3ml acceptation wla refus wla mazal fi en attent

if (!empty($mesReclamations)): ?>
<div class="form-page-header" style="margin-top:32px;">
    <div class="form-page-icon form-page-icon--red">
        <i class="ti ti-history"></i>
    </div>
    <div>
        <h2 class="form-page-title">Mes réclamations</h2>
        <p class="form-page-sub"><?= count($mesReclamations) ?> réclamation(s) soumise(s)</p>
    </div>
</div>

<?php
$statutConfig = [
    'EN_ATTENTE'                    => ['label' => 'En attente',          'class' => 'badge--yellow'],
    'ACCEPTEE_PAR_ADMINISTRATEUR'   => ['label' => 'Transmise au prof',   'class' => 'badge--blue'],
    'REFUSEE_PAR_ADMINISTRATEUR'    => ['label' => 'Refusée (admin)',     'class' => 'badge--red'],
    'ACCEPTEE_PAR_LE_PROFESSEUR'    => ['label' => 'Note modifiée',       'class' => 'badge--green'],
    'REFUSEE_PAR_LE_PROFESSEUR'     => ['label' => 'Refusée (prof)',      'class' => 'badge--red'],
];
?>

<?php foreach ($mesReclamations as $r): ?>
<?php $s = $statutConfig[$r['statut']] ?? ['label' => $r['statut'], 'class' => 'badge--yellow']; ?>
<div class="card recl-card">

    <div class="recl-header">
        <div class="recl-meta">
            <span class="recl-student"><?= htmlspecialchars($r['matiere_nom']) ?></span>
            <span class="recl-num"><?= htmlspecialchars($r['type_eval']) ?></span>
        </div>
        <span class="badge <?= $s['class'] ?>"><?= $s['label'] ?></span>
    </div>

    <div class="recl-body">

        <div class="recl-info-row">
            <span class="recl-label">Note actuelle</span>
            <span class="recl-val"><?= $r['note_actuelle'] ?>/20</span>
        </div>

        <?php if (!empty($r['note_nouvelle'])): ?>
        <div class="recl-info-row">
            <span class="recl-label">Nouvelle note</span>
            <span class="recl-val" style="color:#4ade80;font-weight:600;">
                <?= $r['note_nouvelle'] ?>/20
            </span>
        </div>
        <?php endif; ?>

        <div class="recl-info-row recl-info-row--full">
            <span class="recl-label">Motif</span>
            <span class="recl-val"><?= htmlspecialchars($r['commentaire']) ?></span>
        </div>

        <?php if (!empty($r['raison_refus'])): ?>
        <div class="recl-info-row recl-info-row--full">
            <span class="recl-label">Raison du refus</span>
            <span class="recl-val" style="color:#f87171;">
                <?= htmlspecialchars($r['raison_refus']) ?>
            </span>
        </div>
        <?php endif; ?>

        <span class="recl-date"><?= $r['date_soumission'] ?></span>

    </div>

</div>
<?php endforeach; ?>

<?php endif; ?>


</main>
</div>



<?php
// nhot les données PHP fi variables JS bch reclamation.js ykhdmhom
$matieresDataJS = [];
foreach ($matieres as $m) {
    $matieresDataJS[$m['id']] = [
        'id'  => $m['id'],
        'nom' => $m['nom'],   // aliasé AS nom dans getMatieres()
    ];
}
?>
<script>
    // data mta3 les notes indexée par matiere_id puis type (DS/EXAM/TP)
    const notesData    = <?= json_encode($notesJS,        JSON_UNESCAPED_UNICODE) ?>;
    // data mta3 les profs indexée par matiere_id
    const profsJS      = <?= json_encode($profsJS,        JSON_UNESCAPED_UNICODE) ?>;
    // data mta3 les matieres indexée par matiere_id
    const matieresData = <?= json_encode($matieresDataJS, JSON_UNESCAPED_UNICODE) ?>;

            // bch ychouf ken fama matiere mselectitha deja w y3ti les info mta3ha fll form
    (function () {
        const sel = document.getElementById("matiere-select");
        if (sel && sel.value) {
            onMatiereChange(sel);
        }
    })();
</script>
<script src="/js/reclamation.js"></script>