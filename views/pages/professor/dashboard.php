<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Professor Workspace Dashboard</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 30px;
            margin-top: 25px;
        }

        .course-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .course-card {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            background: #fff;
            text-decoration: none;
            color: #333;
            font-weight: bold;
            transition: all 0.2s;
        }

        .course-card.active {
            border-color: #0d6efd;
            background: #e7f1ff;
            color: #0d6efd;
        }

        .main-panel {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 25px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .exam-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .exam-table th,
        .exam-table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            text-align: left;
        }

        .exam-table th {
            background: #f8f9fa;
        }

        /* Modal Window Styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            width: 450px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 0.9rem;
        }

        .form-group select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div
        style="background: #fff3cd; border: 1px solid #ffe69c; padding: 15px; margin: 20px auto; max-width: 1400px; border-radius: 6px; font-family: monospace;">
        <h4>🔍 Dashboard Data Spy</h4>
        <p><strong>Selected Course ID:</strong>
            <?= var_export($selectedCourseId, true) ?>
        </p>
        <p><strong>Found Courses:</strong>
            <?= count($courses) ?>
        </p>
        <p><strong>Found Exams for this Course:</strong>
            <?= count($exams) ?>
        </p>
        <hr>
        <strong>Raw Exams Array Dump:</strong>
        <pre><?php print_r($exams); ?></pre>
    </div>

    <div class="container" style="padding: 30px; max-width: 1400px; margin: 0 auto;">
        <h2>Professor Management Hub</h2>
        <hr>

        <div class="dashboard-grid">
            <div class="course-list">
                <h3>My Courses</h3>
                <?php foreach ($courses as $c): ?>
                    <a href="?course_id=<?= $c['id'] ?>"
                        class="course-card <?= $c['id'] === $selectedCourseId ? 'active' : '' ?>">
                        ▼ <?= htmlspecialchars($c['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="main-panel">
                <div class="panel-header">
                    <h3>Exams & Evaluations Log</h3>
                    <?php if ($selectedCourseId > 0): ?>
                        <button class="btn btn-primary" onclick="toggleModal(true)">+ Create New Exam</button>
                    <?php endif; ?>
                </div>

                <?php if (empty($exams)): ?>
                    <p class="text-muted">No evaluation slots registered inside this educational path profile.</p>
                <?php else: ?>
                    <table class="exam-table">
                        <thead>
                            <tr>
                                <th>Exam ID</th>
                                <th>Type</th>
                                <th>Formatting Model</th>
                                <th>Grading Status</th>
                                <th>Global Mark</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($exams as $exam): ?>
                                <tr>
                                    <td><strong>#<?= $exam['id'] ?></strong></td>
                                    <td><span class="badge"><?= $exam['type'] ?></span></td>
                                    <td><code><?= $exam['format'] ?></code></td>
                                    <td><?= $exam['statut'] ?></td>
                                    <td><?= $exam['note'] !== null ? $exam['note'] . ' / 20' : '<i>Not Evaluated</i>' ?></td>
                                    <td>
                                        <?php if ($exam['format'] !== 'NON_QCM'): ?>
                                            <a href="/professor/qcm/create?exam_id=<?= $exam['id'] ?>"
                                                style="font-size: 0.85rem; color:#0d6efd; margin-right:10px;">Config Key</a>
                                        <?php endif; ?>
                                        <a href="/professor/qcm/scan?exam_id=<?= $exam['id'] ?>"
                                            style="font-size: 0.85rem; color:#198754;">Scan Sheets</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="createExamModal" class="modal">
        <div class="modal-content">
            <h4>Setup New Evaluation Parameters</h4>
            <hr style="margin-bottom: 15px;">
            <form action="/professor/exam/create" method="POST">
                <input type="hidden" name="enseignement_id" value="<?= $selectedCourseId ?>">

                <div class="form-group">
                    <label>Evaluation Target Profile (Type)</label>
                    <select name="type" required>
                        <option value="DS">DS (Devoir de Contrôle)</option>
                        <option value="EXAM">EXAM (Examen Principal)</option>
                        <option value="TP">TP (Travaux Pratiques)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Structural Formatting Model</label>
                    <select name="format" required>
                        <option value="QCM">QCM (Fully Automated Bubble Mapping)</option>
                        <option value="MIX">MIX (Hybrid - Structural Questions + Manual Writing)</option>
                        <option value="NON_QCM">NON_QCM (Traditional Handwritten Evaluation)</option>
                    </select>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="toggleModal(false)">Cancel</button>
                    <button type="submit" class="btn btn-success">Initialize Profile</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(show) {
            document.getElementById('createExamModal').style.display = show ? 'flex' : 'none';
        }

        // Capture hybrid MIX confirmation rules injected via redirect URL flags
        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('prompt_mix_qcm')) {
                const mixExamId = urlParams.get('prompt_mix_qcm');
                const userChoice = confirm(`Hybrid MIX evaluation profile #${mixExamId} initialized!\n\nWould you like to configure its optical QCM bubble answer matrix right now?`);
                if (userChoice) {
                    window.location.href = `/professor/qcm/create?exam_id=${mixExamId}`;
                }
            }
        });
    </script>
</body>

</html>