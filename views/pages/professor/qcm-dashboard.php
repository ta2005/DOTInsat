<?php
// views/pages/professor/qcm-dashboard.php

$config = [
    'nav' => [
        ['href' => '/?page=home',             'label' => 'Accueil'],
        ['href' => '/?page=forum',             'label' => 'Blog'],
        ['href' => '/?page=examens-prof',      'label' => 'Examens'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];

require BASE_PATH . '/views/layouts/header.php';

// Cours actif
$currentCourse = null;
if ($selectedCourseId > 0) {
    $matches       = array_filter($courses, fn($c) => (int)$c['id'] === $selectedCourseId);
    $currentCourse = reset($matches) ?: null;
}

// Groupement DS / EXAM / TP
$examGroups = ['DS' => [], 'EXAM' => [], 'TP' => []];
foreach ($exams ?? [] as $e) {
    $examGroups[strtoupper($e['type'] ?? 'DS')][] = $e;
}
$examMeta = [
    'DS'   => ['label' => 'Devoirs de contrôle', 'css' => 'ds'],
    'EXAM' => ['label' => 'Examens principaux',  'css' => 'exam'],
    'TP'   => ['label' => 'Travaux pratiques',   'css' => 'tp'],
];
?>

<link rel="stylesheet" href="/css/qcm.css">

<div class="qcm-wrap">
    <h2 class="page-title">QCM — Tableau de bord</h2>
    <p class="page-sub">Gérez vos examens et configurations de correction optique.</p>

    <div class="dash-grid">

        <!-- ── Sidebar cours ── -->
        <div class="card">
            <div class="sidebar-title">Mes cours</div>
            <?php if (empty($courses)): ?>
                <div class="empty" style="padding:20px">Aucun cours disponible.</div>
            <?php else: ?>
                <?php foreach ($courses as $c): ?>
                    <a href="?page=qcm-dashboard&course_id=<?= (int)$c['id'] ?>"
                       class="course-link <?= (int)$c['id'] === $selectedCourseId ? 'active' : '' ?>">
                        <?= htmlspecialchars($c['nom']) ?>
                        <?php if (!empty($c['code'])): ?>
                            <div class="meta"><?= htmlspecialchars($c['code']) ?></div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ── Card principale ── -->
        <div class="card">

            <div class="card-head">
                <span><?= $currentCourse ? htmlspecialchars($currentCourse['nom']) : 'Sélectionnez un cours' ?></span>
                <?php if ($currentCourse): ?>
                    <button class="btn btn-blue btn-sm" onclick="openModal('modalCreate')">+ Nouvel examen</button>
                <?php endif; ?>
            </div>

            <?php if (!$currentCourse): ?>

                <div class="empty">Choisissez un cours dans la barre latérale.</div>

            <?php elseif (empty($exams)): ?>

                <div class="empty">Aucun examen enregistré. Créez-en un avec le bouton ci-dessus.</div>

            <?php else: ?>

                <?php foreach ($examGroups as $groupType => $groupRows):
                    $groupLabel = $examMeta[$groupType]['label'];
                    $groupCss   = $examMeta[$groupType]['css'];
                ?>

                <div class="type-head <?= $groupCss ?>">
                    <?= htmlspecialchars($groupLabel) ?>
                    <span class="count"><?= count($groupRows) ?></span>
                </div>

                <?php if (empty($groupRows)): ?>

                    <div class="type-empty">Aucun <?= strtolower($groupLabel) ?> pour ce cours.</div>

                <?php else: ?>

                    <?php $showStudent = ($groupType !== 'EXAM'); ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <?php if ($showStudent): ?>
                                    <th>Étudiant</th>
                                <?php endif; ?>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Note</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groupRows as $exam):
                                $examId      = (int)$exam['id'];
                                $type        = strtoupper($exam['type'] ?? 'DS');
                                $statut      = strtolower($exam['statut'] ?? '');
                                $badgeClass  = match($type) { 'QCM' => 'badge-qcm', 'MIX' => 'badge-mix', default => 'badge-nonqcm' };
                                $statusClass = match($statut) { 'attente' => 'status-attente', 'corrige' => 'status-corrige', default => 'status-default' };
                                $statusLabel = match($statut) { 'attente' => 'En attente', 'corrige' => 'Corrigé', default => ucfirst($statut ?: '—') };
                                $etudiantNom = trim(($exam['etudiant_prenom'] ?? '') . ' ' . ($exam['etudiant_nom'] ?? '')) ?: '—';
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($exam['titre'] ?? '—') ?></td>
                                <?php if ($showStudent): ?>
                                    <td><?= htmlspecialchars($etudiantNom) ?></td>
                                <?php endif; ?>
                                <td><span class="badge <?= $badgeClass ?>"><?= $type ?></span></td>
                                <td><span class="status <?= $statusClass ?>"><?= $statusLabel ?></span></td>
                                <td>
                                    <div class="note-field">
                                        <input type="number" id="note_<?= $examId ?>"
                                               min="0" max="20" step="0.25"
                                               value="<?= htmlspecialchars($exam['note'] ?? '') ?>"
                                               placeholder="—">
                                        <button onclick="saveNote(<?= $examId ?>)">✓</button>
                                    </div>
                                </td>
                                <td>
                                    <?php if (in_array($type, ['QCM', 'MIX'])): ?>
                                        <a href="?page=qcm-create&exam_id=<?= $examId ?>" class="btn btn-gray btn-sm">Matrice</a>
                                        <a href="?page=qcm-scanner&exam_id=<?= $examId ?>" class="btn btn-gray btn-sm">Scanner</a>
                                    <?php endif; ?>
                                    <a href="?page=exam-detail&exam_id=<?= $examId ?>" class="btn btn-gray btn-sm">Détail</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php endif; ?>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<!-- ── Modal : créer un examen ── -->
<div id="modalCreate" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <span>Nouvel examen</span>
            <button onclick="closeModal('modalCreate')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-field">
                <label>Type d'examen</label>
                <select id="newExamType">
                    <option value="DS">Devoir de contrôle</option>
                    <option value="EXAM">Examen principal</option>
                    <option value="TP">Travaux pratiques</option>
                </select>
            </div>
            <div class="form-field">
                <label>Format</label>
                <select id="newExamFormat">
                    <option value="QCM">QCM</option>
                    <option value="MIX">MIX</option>
                    <option value="CLASSIC">Classique</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-gray" onclick="closeModal('modalCreate')">Annuler</button>
            <button class="btn btn-blue">Créer</button>
        </div>
    </div>
</div>

<!-- ── Modal : confirmation note ── -->
<div id="modalNote" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <span>Note</span>
            <button onclick="closeModal('modalNote')">✕</button>
        </div>
        <div class="modal-body">
            <p id="noteMsg" style="font-size:.95rem"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-gray" onclick="closeModal('modalNote')">Fermer</button>
        </div>
    </div>
</div>

<script>
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});

async function saveNote(examId) {
    const input = document.getElementById('note_' + examId);
    const val   = parseFloat(input.value);
    if (isNaN(val) || val < 0 || val > 20) { showNote('⚠ Note invalide (0–20).', false); return; }
    try {
        const r = await fetch('?page=api-save-note', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ exam_id: examId, note: val }),
        });
        showNote(r.ok ? `✓ Note ${val}/20 enregistrée.` : '✗ Erreur serveur.', r.ok);
    } catch { showNote('✗ Connexion impossible.', false); }
}

function showNote(msg, ok) {
    const p = document.getElementById('noteMsg');
    p.textContent = msg;
    p.style.color = ok ? '#4ade80' : '#ef4444';
    openModal('modalNote');
}

window.addEventListener('DOMContentLoaded', () => {
    const p = new URLSearchParams(location.search);
    if (p.has('prompt_mix_qcm')) {
        const id = p.get('prompt_mix_qcm');
        if (confirm(`Examen MIX #${id} créé. Configurer la matrice QCM ?`))
            location.href = `?page=qcm-create&exam_id=${id}`;
    }
});
</script>