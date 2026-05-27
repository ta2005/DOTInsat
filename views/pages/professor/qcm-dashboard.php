<?php
// views/pages/professor/qcm-dashboard.php

// Nav config pour le header
$config = [
    'nav' => [
        ['href' => '/?page=home',             'label' => 'Accueil'],
        ['href' => '/?page=examens-prof',      'label' => 'Examens'],
        ['href' => '/?page=qcm-dashboard',     'label' => 'QCM Dashboard'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];

require BASE_PATH . '/views/layouts/header.php';
?>

<style>
    /* ── Variables ── */
    :root {
        --bg:         #f4f6f9;
        --surface:    #ffffff;
        --border:     #e2e8f0;
        --primary:    #1a56db;
        --primary-lt: #ebf0ff;
        --success:    #0d9488;
        --warning:    #d97706;
        --danger:     #dc2626;
        --text:       #1e293b;
        --muted:      #64748b;
        --radius:     8px;
        --shadow:     0 1px 4px rgba(0,0,0,.08);
    }

    body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', sans-serif; }

    /* ── Layout ── */
    .dash-wrap {
        max-width: 1300px;
        margin: 30px auto;
        padding: 0 20px;
    }

    .dash-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
    .dash-sub   { color: var(--muted); font-size: .9rem; margin-bottom: 24px; }

    .dash-grid {
        display: grid;
        grid-template-columns: 260px 1fr;
        gap: 24px;
        align-items: start;
    }

    /* ── Sidebar ── */
    .sidebar-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .sidebar-head {
        padding: 14px 16px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .course-item {
        display: block;
        padding: 13px 16px;
        text-decoration: none;
        color: var(--text);
        font-size: .9rem;
        border-bottom: 1px solid var(--border);
        transition: background .15s;
    }
    .course-item:last-child { border-bottom: none; }
    .course-item:hover      { background: #f1f5f9; }

    .course-item.active {
        background: var(--primary-lt);
        color: var(--primary);
        font-weight: 600;
        border-left: 3px solid var(--primary);
    }

    .course-meta {
        font-size: .75rem;
        color: var(--muted);
        margin-top: 2px;
    }

    /* ── Main panel ── */
    .main-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .main-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .main-head h3 { font-size: 1rem; font-weight: 600; margin: 0; }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        border: none;
        text-decoration: none;
        transition: opacity .15s, transform .1s;
    }
    .btn:hover   { opacity: .88; transform: translateY(-1px); }
    .btn:active  { transform: translateY(0); }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-ghost   { background: transparent; color: var(--primary); border: 1px solid var(--primary); }
    .btn-sm      { padding: 5px 10px; font-size: .8rem; }

    /* ── Table ── */
    .exam-table { width: 100%; border-collapse: collapse; }

    .exam-table th {
        padding: 11px 16px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: var(--muted);
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }

    .exam-table td {
        padding: 13px 16px;
        border-bottom: 1px solid var(--border);
        font-size: .88rem;
        vertical-align: middle;
    }

    .exam-table tr:last-child td { border-bottom: none; }
    .exam-table tr:hover td      { background: #fafbfc; }

    /* ── Badges ── */
    .badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: .04em;
    }
    .badge-qcm    { background: #dbeafe; color: #1d4ed8; }
    .badge-mix    { background: #fef3c7; color: #92400e; }
    .badge-nonqcm { background: #f1f5f9; color: #475569; }

    .status-badge { padding: 3px 9px; border-radius: 4px; font-size: .75rem; font-weight: 600; }
    .status-en-attente { background: #fef9c3; color: #854d0e; }
    .status-corrige    { background: #dcfce7; color: #166534; }
    .status-default    { background: #f1f5f9; color: #475569; }

    /* ── Empty state ── */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--muted);
    }
    .empty-state svg { margin-bottom: 12px; opacity: .35; }
    .empty-state p   { font-size: .95rem; }

    /* ── Modal ── */
    .modal-overlay {
        display: none;
        position: fixed; inset: 0;
        background: rgba(15,23,42,.45);
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .modal-overlay.open { display: flex; }

    .modal-box {
        background: var(--surface);
        border-radius: 10px;
        width: 440px;
        max-width: 95vw;
        box-shadow: 0 8px 32px rgba(0,0,0,.18);
        animation: modalIn .2s ease;
    }
    @keyframes modalIn {
        from { opacity:0; transform: translateY(-12px) scale(.97); }
        to   { opacity:1; transform: translateY(0)     scale(1);   }
    }

    .modal-header {
        padding: 18px 22px 14px;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-header h4 { margin: 0; font-size: 1rem; font-weight: 700; }
    .modal-close { background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--muted); line-height:1; }
    .modal-close:hover { color: var(--text); }

    .modal-body   { padding: 20px 22px; }
    .modal-footer { padding: 14px 22px; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }

    .form-group        { margin-bottom: 16px; }
    .form-group label  { display: block; font-size: .85rem; font-weight: 600; margin-bottom: 6px; }
    .form-group select {
        width: 100%; padding: 9px 12px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: .9rem;
        background: #fff;
        color: var(--text);
    }
    .form-group select:focus { outline: 2px solid var(--primary); border-color: transparent; }
</style>

<div class="dash-wrap">
    <h2 class="dash-title">QCM — Tableau de bord</h2>
    <p class="dash-sub">Gérez vos examens et configurations de correction optique.</p>

    <div class="dash-grid">

        <!-- ══ Sidebar : liste des cours ══ -->
        <div class="sidebar-card">
            <div class="sidebar-head">Mes cours</div>

            <?php if (empty($courses)): ?>
                <div style="padding:16px; font-size:.85rem; color:var(--muted);">
                    Aucun cours assigné.
                </div>
            <?php else: ?>
                <?php foreach ($courses as $c): ?>
                    <a href="?page=qcm-dashboard&course_id=<?= (int)$c['id'] ?>"
                       class="course-item <?= (int)$c['id'] === $selectedCourseId ? 'active' : '' ?>">
                        <?= htmlspecialchars($c['nom']) ?>
                        <?php if (!empty($c['filiere']) || !empty($c['annee'])): ?>
                            <div class="course-meta">
                                <?= htmlspecialchars(trim(($c['filiere'] ?? '') . ' — ' . ($c['annee'] ?? ''), ' —')) ?>
                            </div>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- ══ Panel principal : examens ══ -->
        <div class="main-card">
            <div class="main-head">
                <h3>
                    <?php if ($selectedCourseId > 0):
                        $activeCourse = array_filter($courses, fn($c) => (int)$c['id'] === $selectedCourseId);
                        $activeCourse = reset($activeCourse);
                        echo 'Examens — ' . htmlspecialchars($activeCourse['nom'] ?? '');
                    else: ?>
                        Sélectionnez un cours
                    <?php endif; ?>
                </h3>

                <?php if ($selectedCourseId > 0): ?>
                    <button class="btn btn-primary" onclick="openModal()">+ Nouvel examen</button>
                <?php endif; ?>
            </div>

            <?php if ($selectedCourseId <= 0): ?>
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p>Choisissez un cours dans la barre latérale pour afficher ses examens.</p>
                </div>

            <?php elseif (empty($exams)): ?>
                <div class="empty-state">
                    <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>Aucun examen enregistré pour ce cours.<br>Créez-en un avec le bouton ci-dessus.</p>
                </div>

            <?php else: ?>
                <table class="exam-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Format</th>
                            <th>Statut</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exams as $exam): ?>
                            <tr>
                                <td><strong>#<?= (int)$exam['id'] ?></strong></td>

                                <td><?= htmlspecialchars($exam['type']) ?></td>

                                <td>
                                    <?php
                                    $fmt = $exam['format'];
                                    $cls = match($fmt) {
                                        'QCM'     => 'badge-qcm',
                                        'MIX'     => 'badge-mix',
                                        default   => 'badge-nonqcm',
                                    };
                                    ?>
                                    <span class="badge <?= $cls ?>"><?= htmlspecialchars($fmt) ?></span>
                                </td>

                                <td>
                                    <?php
                                    $st  = $exam['statut'] ?? '';
                                    $scls = match($st) {
                                        'EN_ATTENTE' => 'status-en-attente',
                                        'CORRIGE'    => 'status-corrige',
                                        default      => 'status-default',
                                    };
                                    ?>
                                    <span class="status-badge <?= $scls ?>"><?= htmlspecialchars($st) ?></span>
                                </td>

                                <td>
                                    <?= $exam['note'] !== null
                                        ? '<strong>' . htmlspecialchars((string)$exam['note']) . '</strong> / 20'
                                        : '<em style="color:var(--muted)">—</em>' ?>
                                </td>

                                <td style="display:flex; gap:8px; flex-wrap:wrap;">
                                    <?php if (in_array($exam['format'], ['QCM', 'MIX'], true)): ?>
                                        <a href="?page=qcm-create&exam_id=<?= (int)$exam['id'] ?>"
                                           class="btn btn-ghost btn-sm">Clé</a>
                                    <?php endif; ?>
                                    <a href="?page=qcm-scan&exam_id=<?= (int)$exam['id'] ?>"
                                       class="btn btn-primary btn-sm">Scanner</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══ Modal : créer un examen ══ -->
<div id="createModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h4>Nouvel examen</h4>
            <button class="modal-close" onclick="closeModal()">×</button>
        </div>

        <form method="POST" action="?page=api-create-exam">
            <div class="modal-body">
                <input type="hidden" name="enseignement_id" value="<?= $selectedCourseId ?>">

                <div class="form-group">
                    <label for="type">Type d'évaluation</label>
                    <select name="type" id="type" required>
                        <option value="DS">DS — Devoir de contrôle</option>
                        <option value="EXAM">EXAM — Examen principal</option>
                        <option value="TP">TP — Travaux pratiques</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="format">Format</label>
                    <select name="format" id="format" required>
                        <option value="QCM">QCM — Correction optique automatique</option>
                        <option value="MIX">MIX — Hybride (QCM + questions ouvertes)</option>
                        <option value="NON_QCM">NON_QCM — Copie manuscrite traditionnelle</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closeModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal()  { document.getElementById('createModal').classList.add('open'); }
    function closeModal() { document.getElementById('createModal').classList.remove('open'); }

    // Fermer en cliquant en dehors
    document.getElementById('createModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    // Redirection post-création de MIX → configurer la clé QCM
    window.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        if (params.has('prompt_mix_qcm')) {
            const examId = params.get('prompt_mix_qcm');
            if (confirm(`Examen MIX #${examId} créé.\nVoulez-vous configurer sa matrice QCM maintenant ?`)) {
                window.location.href = `?page=qcm-create&exam_id=${examId}`;
            }
        }
    });
</script>