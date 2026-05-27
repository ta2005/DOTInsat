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

<link rel="stylesheet" href="<?= BASE_URL ?>/css/prof.css">

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
                                                                <th>Note</th>
                                                                <th>Actions</th>
                                                            </tr>

                                                        </thead>

                                                        <tbody>

                                                            <?php foreach ($items as $exam): ?>

                                                                    <tr>

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

                                                                            <?=
                                                                                $exam['note'] !== null
                                                                                ? '<strong>' .
                                                                                htmlspecialchars((string) $exam['note']) .
                                                                                '</strong> / 20'
                                                                                : '--'
                                                                                ?>

                                                                        </td>

                                                                        <td class="actions-cell">

                                                                            <?php if (
                                                                                in_array(
                                                                                    $exam['format'],
                                                                                    ['QCM', 'MIX'],
                                                                                    true
                                                                                )
                                                                            ): ?>

                                                                                    <a href="?page=qcm-create&exam_id=<?= (int) $exam['id'] ?>"
                                                                                        class="btn btn-secondary btn-sm">
                                                                                        Clé
                                                                                    </a>

                                                                            <?php endif; ?>

                                                                            <a href="?page=qcm-scan&exam_id=<?= (int) $exam['id'] ?>"
                                                                                class="btn btn-primary btn-sm">
                                                                                Scanner
                                                                            </a>

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

<script>

    function openModal() {

        document
            .getElementById('createModal')
            .classList
            .add('open');
    }

    function closeModal() {

        document
            .getElementById('createModal')
            .classList
            .remove('open');
    }

    document
        .getElementById('createModal')
        .addEventListener('click', function (e) {

            if (e.target === this) {
                closeModal();
            }
        });

</script>