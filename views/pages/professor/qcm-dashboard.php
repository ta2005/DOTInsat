<?php
// views/pages/professor/qcm-dashboard.php

$config = [
    'nav' => [
        ['href' => '/?page=home', 'label' => 'Accueil'],
        ['href' => '/?page=forum', 'label' => 'Blog'],
        ['href' => '/?page=examens-prof', 'label' => 'Examens'],
        ['href' => '/?page=prof-reclamations', 'label' => 'Réclamations'],
    ]
];

require BASE_PATH . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof-dashboard.css">

<div class="calculator-page">
    <div class="container">
        <div class="calculator-wrapper">

            <div class="dash-wrap">

                <div class="dashboard-header">
                    <h1>Dashboard QCM</h1>
                    <p>Gestion des examens et correction automatique</p>
                </div>

                <div class="dash-grid">


                    <!-- SIDEBAR -->
                    <div class="sidebar-card">

                        <div class="sidebar-title">
                            Mes cours
                        </div>

                        <?php if (empty($courses)): ?>

                                <div class="empty-sidebar">
                                    Aucun cours disponible
                                </div>

                        <?php else: ?>

                                <?php foreach ($courses as $c): ?>

                                        <a href="?page=qcm-dashboard&course_id=<?= (int) $c['id'] ?>"
                                            class="course-item <?= (int) $c['id'] === $selectedCourseId ? 'active' : '' ?>">

                                            <div class="course-name">
                                                <?= htmlspecialchars($c['nom']) ?>
                                            </div>

                                            <?php if (!empty($c['filiere']) || !empty($c['annee'])): ?>

                                                    <div class="course-meta">
                                                        <?= htmlspecialchars(
                                                            trim(
                                                                ($c['filiere'] ?? '') .
                                                                ' — ' .
                                                                ($c['annee'] ?? ''),
                                                                ' —'
                                                            )
                                                        ) ?>
                                                    </div>

                                            <?php endif; ?>

                                        </a>

                                <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                    <!-- MAIN -->
                    <div class="main-card">

                        <div class="main-header">

                            <div>
                                <h2>

                                    <?php if ($selectedCourseId > 0):

                                        $activeCourse = array_filter(
                                            $courses,
                                            fn($c) => (int) $c['id'] === $selectedCourseId
                                        );

                                        $activeCourse = reset($activeCourse);

                                        echo htmlspecialchars($activeCourse['nom'] ?? '');

                                    else: ?>

                                            Sélectionnez un cours

                                    <?php endif; ?>

                                </h2>

                                <span>
                                    Tableau des examens
                                </span>
                            </div>

                            <?php if ($selectedCourseId > 0): ?>

                                    <button class="btn btn-primary" onclick="openModal()">
                                        + Nouvel examen
                                    </button>

                            <?php endif; ?>

                        </div>

                        <?php if ($selectedCourseId <= 0): ?>

                                <div class="empty-state">
                                    Sélectionnez un cours pour afficher les examens
                                </div>

                        <?php else: ?>

                                <?php
                                $groupedExams = [
                                    'TP' => [],
                                    'DS' => [],
                                    'EXAM' => []
                                ];

                                foreach ($exams as $exam) {

                                    $type = strtoupper($exam['type']);

                                    if (isset($groupedExams[$type])) {
                                        $groupedExams[$type][] = $exam;
                                    }
                                }
                                ?>

                                <?php foreach ($groupedExams as $section => $items): ?>

                                        <div class="exam-section">

                                            <div class="section-header">

                                                <h3 class="section-title">
                                                    <?= $section ?>
                                                </h3>

                                                <span class="section-count">
                                                    <?= count($items) ?> examen(s)
                                                </span>

                                            </div>

                                            <?php if (empty($items)): ?>

                                                    <div class="empty-category">
                                                        Aucun <?= $section ?>
                                                    </div>

                                            <?php else: ?>

                                                    <table class="exam-table">

                                                        <thead>

                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Type</th>
                                                                <th>Format</th>
                                                                <th>Statut</th>
                                                                <th>Étudiants</th>
                                                                <th>Actions</th>
                                                            </tr>

                                                        </thead>

                                                        <tbody>

                                                            <?php foreach ($items as $exam): ?>

                                                                    <tr onclick="toggleStudents(<?= (int)$exam['id'] ?>)" style="cursor: pointer;" class="exam-row-clickable">

                                                                        <td>
                                                                            <strong>
                                                                                #<?= (int) $exam['id'] ?>
                                                                            </strong>
                                                                        </td>

                                                                        <td>

                                                                            <span class="type-badge">
                                                                                <?= htmlspecialchars($exam['type']) ?>
                                                                            </span>

                                                                        </td>

                                                                        <td>

                                                                            <span class="format-badge">

                                                                                <?= htmlspecialchars($exam['format']) ?>

                                                                            </span>

                                                                        </td>

                                                                        <td>

                                                                            <?php
                                                                            $statusClass =
                                                                                ($exam['statut'] ?? '') === 'CORRIGE'
                                                                                ? 'status-corrige'
                                                                                : 'status-en-attente';
                                                                            ?>

                                                                            <span class="status-badge <?= $statusClass ?>">

                                                                                <?= htmlspecialchars($exam['statut']) ?>

                                                                            </span>

                                                                        </td>

                                                                        <td>
                                                                            <span class="toggle-students-btn" style="color: #3b82f6; font-weight: bold; font-size: 14px;">
                                                                                <?= count($exam['students']) ?> étudiant(s) ▾
                                                                            </span>
                                                                        </td>

                                                                        <td class="actions-cell" onclick="event.stopPropagation();">

                                                                            <!-- Delete Exam -->
                                                                            <button class="btn btn-danger btn-sm" onclick="if(confirm('Êtes-vous sûr de vouloir supprimer cet examen ?')) location.href='?page=exam-delete&exam_id=<?= (int) $exam['id'] ?>'">Supprimer</button>
                                                                            <!-- Modify Exam -->
                                                                            <button class="btn btn-warning btn-sm" onclick="openModifyModal(<?= (int) $exam['id'] ?>, '<?= htmlspecialchars($exam['format']) ?>')">Modifier</button>

                                                                        </td>

                                                                    </tr>

                                                                    <tr id="students-drawer-<?= (int)$exam['id'] ?>" class="students-drawer-row" style="display: none;">
                                                                        <td colspan="6" style="padding: 0;">
                                                                            <div class="students-drawer-content">
                                                                                <h4>Résultats des étudiants</h4>
                                                                                <?php if (empty($exam['students'])): ?>
                                                                                    <p class="no-students">Aucun étudiant n'a encore de note pour cet examen.</p>
                                                                                <?php else: ?>
                                                                                    <table class="students-subtable">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>CIN</th>
                                                                                                <th>Nom & Prénom</th>
                                                                                                <th>Email</th>
                                                                                                <th>Statut Note</th>
                                                                                                <th>Note</th>
                                                                                                <th>Actions</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                            <?php foreach ($exam['students'] as $s): ?>
                                                                                                <tr>
                                                                                                    <td><?= htmlspecialchars((string)$s['cin']) ?></td>
                                                                                                    <td><?= htmlspecialchars($s['prenom'] . ' ' . $s['nom']) ?></td>
                                                                                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                                                                                    <td>
                                                                                                        <span class="status-badge <?= $s['statut'] === 'CORRIGE' || $s['statut'] === 'VERIFIE' ? 'status-corrige' : 'status-en-attente' ?>">
                                                                                                            <?= htmlspecialchars($s['statut']) ?>
                                                                                                        </span>
                                                                                                    </td>
                                                                                                    <td>
                                                                                                        <?php if ($s['note'] !== null): ?>
                                                                                                            <strong><?= htmlspecialchars((string)$s['note']) ?></strong> / 20
                                                                                                        <?php else: ?>
                                                                                                            <strong>--</strong>
                                                                                                        <?php endif; ?>
                                                                                                    </td>
                                                                                                     <td style="display:flex; gap:6px; align-items:center;">
                                                                                                         <?php if ($s['statut'] === 'CORRIGE' || $s['statut'] === 'VERIFIE'): ?>
                                                                                                             <button class="btn btn-secondary btn-sm" disabled style="opacity: 0.5; cursor: not-allowed;">Corriger</button>
                                                                                                         <?php elseif ($exam['format'] === 'NON_QCM'): ?>
                                                                                                             <button class="btn btn-secondary btn-sm"
                                                                                                                     data-exam-id="<?= (int)$exam['id'] ?>"
                                                                                                                     data-student-id="<?= (int)$s['student_id'] ?>"
                                                                                                                     data-cin="<?= htmlspecialchars((string)$s['cin'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                     data-fullname="<?= htmlspecialchars($s['prenom'] . ' ' . $s['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                     data-email="<?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                     data-note="<?= $s['note'] !== null ? htmlspecialchars((string)$s['note'], ENT_QUOTES, 'UTF-8') : '' ?>"
                                                                                                                     data-statut="<?= htmlspecialchars($s['statut'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                     onclick="openStudentModifyModal(this)">
                                                                                                                 Corriger
                                                                                                             </button>
                                                                                                         <?php else: ?>
                                                                                                             <button class="btn btn-secondary btn-sm" onclick="location.href='?page=qcm-scan&exam_id=<?= (int)$exam['id'] ?>&student_id=<?= (int)$s['student_id'] ?>&student_cin=<?= htmlspecialchars((string)$s['cin'], ENT_QUOTES, 'UTF-8') ?>'">Corriger</button>
                                                                                                         <?php endif; ?>
                                                                                                         <button class="btn btn-secondary btn-sm"
                                                                                                                 data-exam-id="<?= (int)$exam['id'] ?>"
                                                                                                                 data-student-id="<?= (int)$s['student_id'] ?>"
                                                                                                                 data-cin="<?= htmlspecialchars((string)$s['cin'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                 data-fullname="<?= htmlspecialchars($s['prenom'] . ' ' . $s['nom'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                 data-email="<?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                 data-note="<?= $s['note'] !== null ? htmlspecialchars((string)$s['note'], ENT_QUOTES, 'UTF-8') : '' ?>"
                                                                                                                 data-statut="<?= htmlspecialchars($s['statut'], ENT_QUOTES, 'UTF-8') ?>"
                                                                                                                 onclick="openStudentModifyModal(this)">
                                                                                                             Modifier
                                                                                                         </button>
                                                                                                     </td>
                                                                                                </tr>
                                                                                            <?php endforeach; ?>
                                                                                        </tbody>
                                                                                    </table>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </td>
                                                                    </tr>

                                                            <?php endforeach; ?>

                                                        </tbody>

                                                    </table>

                                            <?php endif; ?>

                                        </div>

                                <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<!-- MODAL -->

<div id="createModal" class="modal-overlay">

    <div class="modal-box">

        <div class="modal-header">

            <h3>Nouvel examen</h3>

            <button class="modal-close" onclick="closeModal()">
                ×
            </button>

        </div>

        <form method="POST" action="?page=api-create-exam">

            <div class="modal-body">

                <input type="hidden" name="enseignement_id" value="<?= $selectedCourseId ?>">

                <div class="form-group">

                    <label for="type">
                        Type d'évaluation
                    </label>

                    <select name="type" id="type" required>

                        <option value="DS">
                            DS — Devoir surveillé
                        </option>

                        <option value="EXAM">
                            EXAM — Examen
                        </option>

                        <option value="TP">
                            TP — Travaux pratiques
                        </option>

                    </select>

                </div>

                <div class="form-group">

                    <label for="format">
                        Format
                    </label>

                    <select name="format" id="format" required>

                        <option value="QCM">
                            QCM
                        </option>

                        <option value="MIX">
                            MIX
                        </option>

                        <option value="NON_QCM">
                            NON_QCM
                        </option>

                    </select>

                </div>

            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-secondary" onclick="closeModal()">
                    Annuler
                </button>

                <button type="submit" class="btn btn-primary">
                    Créer
                </button>

            </div>

        </form>

    </div>

</div>

<!-- MODIFY MODAL -->
<div id="modifyModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Modifier l'examen</h3>
            <button class="modal-close" onclick="closeModifyModal()">×</button>
        </div>
        <form method="POST" action="?page=api-modify-exam">
            <div class="modal-body">
                <input type="hidden" name="exam_id" id="modify_exam_id" value="">
                <div class="form-group">
                    <label for="modify_format">Nouveau Format</label>
                    <select name="format" id="modify_format" required>
                        <option value="QCM">QCM</option>
                        <option value="MIX">MIX</option>
                        <option value="NON_QCM">NON_QCM</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModifyModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- CONFIRMATION MODAL -->
<div id="confirmQcmModal" class="modal-overlay">

    <div class="modal-box">

        <div class="modal-header">

            <h3>Examen Créé !</h3>

            <button class="modal-close" onclick="closeConfirmModal()">
                ×
            </button>

        </div>

        <div class="modal-body">

            <p style="font-size: 15px; line-height: 1.5; color: #ffffff; margin: 0;">
                L'examen a été créé avec succès. Voulez-vous générer le modèle de QCM pour cet examen maintenant ?
            </p>

        </div>

        <div class="modal-footer">

            <button type="button" class="btn btn-secondary" id="btnQcmLater">
                Plus tard
            </button>

            <button type="button" class="btn btn-primary" id="btnQcmNow">
                Générer le QCM
            </button>

        </div>

    </div>

</div>

<!-- STUDENT MODIFY MODAL -->
<div id="studentModifyModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Modifier la note de l'étudiant</h3>
            <button class="modal-close" onclick="closeStudentModifyModal()">×</button>
        </div>
        <form method="POST" action="?page=api-modify-student-grade">
            <div class="modal-body">
                <input type="hidden" name="exam_id" id="student_modify_exam_id" value="">
                <input type="hidden" name="student_id" id="student_modify_student_id" value="">
                
                <div class="student-info-summary" style="margin-bottom: 20px; padding: 12px; background: rgba(255,255,255,0.05); border-radius: 6px; font-size: 14px; border: 1px solid rgba(255,255,255,0.1);">
                    <p style="margin: 0 0 6px 0; color: #b3b3b3;"><strong>Nom & Prénom:</strong> <span id="student_modify_name" style="color: #ffffff;"></span></p>
                    <p style="margin: 0 0 6px 0; color: #b3b3b3;"><strong>CIN:</strong> <span id="student_modify_cin" style="color: #ffffff;"></span></p>
                    <p style="margin: 0; color: #b3b3b3;"><strong>Email:</strong> <span id="student_modify_email" style="color: #ffffff;"></span></p>
                </div>

                <div class="form-group">
                    <label for="student_modify_note">Note / 20 (Laisser vide pour "--")</label>
                    <input type="number" name="note" id="student_modify_note" step="0.01" min="0" max="20" placeholder="Note ou vide">
                </div>

                <div class="form-group">
                    <label for="student_modify_statut">Statut Note</label>
                    <select name="statut" id="student_modify_statut" required>
                        <option value="EN_ATTENTE">EN_ATTENTE</option>
                        <option value="CORRIGE">CORRIGE</option>
                        <option value="VERIFIE">VERIFIE</option>
                        <option value="CONTESTE">CONTESTE</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeStudentModifyModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script src="/js/qcm-dashboard.js"></script>

