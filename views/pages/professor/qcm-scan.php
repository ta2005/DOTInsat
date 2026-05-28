<?php
// views/pages/professor/qcm-scan.php

$selectedExamId = isset($_GET['exam_id']) ? (int) $_GET['exam_id'] : 14;
$selectedStudentId = isset($_GET['student_id']) ? (int) $_GET['student_id'] : 0;
$selectedStudentCin = isset($_GET['student_cin']) ? (int) $_GET['student_cin'] : 1002;

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

            <div class="scanner-wrap">

                <h2>
                    Scanner les feuilles QCM
                </h2>

                <div class="scanner-filter" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

                    <input type="hidden" id="scanRealStudentId"
                        value="<?= $selectedStudentId > 0 ? $selectedStudentId : '' ?>">
                    <input type="hidden" id="scanExamId" value="<?= $selectedExamId ?>">
                    <input type="hidden" id="scanStudentId" value="<?= $selectedStudentCin ?>">

                    <div class="input-wrapper" style="width: 100%;">

                        <label for="scanExamIdSelect">
                            Choisir l'examen
                        </label>

                        <select id="scanExamIdSelect" class="form-select"
                            style="background: rgba(255,255,255,0.07); color: #fff; border: 1px solid rgba(255,255,255,0.15); padding: 10px 12px; border-radius: 6px; width: 100%; font-size: 14px; outline: none; transition: border-color 0.2s;">
                            <option value="">Sélectionner un examen...</option>
                        </select>

                    </div>

                    <div class="input-wrapper" style="width: 100%;">

                        <label for="scanStudentIdSelect">
                            Choisir l'étudiant
                        </label>

                        <select id="scanStudentIdSelect" class="form-select"
                            style="background: rgba(255,255,255,0.07); color: #fff; border: 1px solid rgba(255,255,255,0.15); padding: 10px 12px; border-radius: 6px; width: 100%; font-size: 14px; outline: none; transition: border-color 0.2s;">
                            <option value="">Sélectionner un étudiant...</option>
                        </select>

                    </div>

                </div>

                <div class="scanner-layout">

                    <!-- LEFT -->

                    <div>

                        <div id="dropzone" class="dropzone-container">

                            <h3>
                                Déposez la feuille ici
                            </h3>

                            <p>
                                PNG / JPG / JPEG
                            </p>

                            <input type="file" id="fileFallbackInput" accept="image/*" style="display:none;">

                            <button type="button" class="btn btn-primary"
                                onclick="document.getElementById('fileFallbackInput').click()">
                                Parcourir
                            </button>

                        </div>

                        <canvas id="processingCanvas"></canvas>

                    </div>

                    <!-- RIGHT -->

                    <div class="results-sidebar">

                        <h3>
                            Résultats
                        </h3>

                        <hr>

                        <div class="result-block">

                            <label>
                                Étudiant
                            </label>

                            <div id="lblSessionStudent">
                                En attente...
                            </div>

                        </div>

                        <div class="result-block">

                            <label>
                                Statut
                            </label>

                            <span id="pillStatus" class="status-badge status-en-attente">
                                En attente
                            </span>

                        </div>

                        <div class="result-block">

                            <label>
                                Note
                            </label>

                            <div id="lblCalculatedScore" class="score-badge">
                                0.00
                            </div>

                        </div>

                        <div class="result-block"
                            style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">

                            <label for="manualGradeInput">
                                Saisie Note Manuelle
                            </label>

                            <div style="display: flex; gap: 8px; margin-top: 8px;">
                                <input type="number" id="manualGradeInput" step="0.01" min="0" max="20"
                                    placeholder="Note / 20"
                                    style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.12); padding: 8px 12px; border-radius: 6px; font-size: 14px; outline: none; width: 100%;">
                                <button type="button" id="btnSubmitManualGrade" class="btn btn-primary"
                                    style="padding: 8px 16px; font-size: 14px;">
                                    Valider
                                </button>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>
</div>

<script>
    // Embedded Exam & Student Datastore from Repository
    const examData = <?= json_encode($exams, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const initialExamId = <?= (int) $selectedExamId ?>;
    const initialStudentId = <?= (int) $selectedStudentId ?>;
    const initialStudentCin = <?= (int) $selectedStudentCin ?>;

    document.addEventListener('DOMContentLoaded', () => {
        const examSelect = document.getElementById('scanExamIdSelect');
        const studentSelect = document.getElementById('scanStudentIdSelect');

        const hiddenRealStudentId = document.getElementById('scanRealStudentId');
        const hiddenExamId = document.getElementById('scanExamId');
        const hiddenStudentId = document.getElementById('scanStudentId');

        // Populate exams dropdown
        examData.forEach(exam => {
            const opt = document.createElement('option');
            opt.value = exam.id;
            opt.textContent = `${exam.type} — ${exam.format} (${exam.course_name})`;
            examSelect.appendChild(opt);
        });

        // Event listener: when exam selection changes
        examSelect.addEventListener('change', () => {
            const selectedExamId = parseInt(examSelect.value) || 0;
            hiddenExamId.value = selectedExamId || '';

            // Clear student dropdown & hidden inputs
            studentSelect.innerHTML = '<option value="">Sélectionner un étudiant...</option>';
            hiddenRealStudentId.value = '';
            hiddenStudentId.value = '';

            const selectedExam = examData.find(e => parseInt(e.id) === selectedExamId);
            if (selectedExam && selectedExam.students) {
                selectedExam.students.forEach(student => {
                    const opt = document.createElement('option');
                    opt.value = student.student_id;
                    opt.setAttribute('data-cin', student.cin);
                    opt.textContent = `${student.prenom} ${student.nom} (CIN: ${student.cin})`;
                    studentSelect.appendChild(opt);
                });
            }
        });

        // Event listener: when student selection changes
        studentSelect.addEventListener('change', () => {
            const selectedStudentId = parseInt(studentSelect.value) || 0;
            hiddenRealStudentId.value = selectedStudentId || '';

            const selectedOption = studentSelect.options[studentSelect.selectedIndex];
            const selectedCin = selectedOption ? (selectedOption.getAttribute('data-cin') || '') : '';
            hiddenStudentId.value = selectedCin;
        });

        // Auto-select initial state if navigated from the dashboard
        if (initialExamId > 0) {
            examSelect.value = initialExamId;
            examSelect.dispatchEvent(new Event('change'));

            if (initialStudentId > 0) {
                studentSelect.value = initialStudentId;
                studentSelect.dispatchEvent(new Event('change'));
            }
        }

        // Manual Grade Submission Handler
        const btnManual = document.getElementById('btnSubmitManualGrade');
        const manualInput = document.getElementById('manualGradeInput');

        if (btnManual && manualInput) {
            btnManual.addEventListener('click', () => {
                const examId = parseInt(hiddenExamId.value) || 0;
                const studentId = parseInt(hiddenRealStudentId.value) || 0;
                const gradeRaw = manualInput.value.trim();

                if (examId <= 0 || studentId <= 0) {
                    alert('Veuillez sélectionner un examen et un étudiant d\'abord.');
                    return;
                }

                if (gradeRaw === '') {
                    alert('Veuillez saisir une note.');
                    return;
                }

                const grade = parseFloat(gradeRaw);
                if (isNaN(grade) || grade < 0 || grade > 20) {
                    alert('La note doit être un nombre compris entre 0 et 20.');
                    return;
                }

                btnManual.disabled = true;
                btnManual.textContent = 'Envoi...';

                const formData = new FormData();
                formData.append('exam_id', examId);
                formData.append('student_id', studentId);
                formData.append('note', grade);
                formData.append('statut', 'CORRIGE');

                fetch('?page=api-modify-student-grade', {
                    method: 'POST',
                    body: formData
                })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            // Dynamically update UI result badges
                            const scorePill = document.getElementById('lblCalculatedScore');
                            const statusPill = document.getElementById('pillStatus');
                            if (scorePill) scorePill.textContent = grade.toFixed(2);
                            if (statusPill) {
                                statusPill.textContent = 'CORRIGE';
                                statusPill.className = 'status-badge status-corrige';
                            }
                            alert('Note manuelle enregistrée avec succès !');
                            manualInput.value = '';
                        } else {
                            alert('Erreur: ' + (res.message || 'Impossible d\'enregistrer la note.'));
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Une erreur réseau s\'est produite.');
                    })
                    .finally(() => {
                        btnManual.disabled = false;
                        btnManual.textContent = 'Valider';
                    });
            });
        }
        
        // Update status pill to show OpenCV is loading
        const pillStatus = document.getElementById('pillStatus');
        if (pillStatus) {
            pillStatus.innerText = "Chargement OpenCV...";
            pillStatus.className = "status-badge status-en-attente";
        }
    });
    
    // Global flag for OpenCV readiness
    window.opencvReady = false;
    
    function onOpenCvReady() {
        console.log("OpenCV.js is fully loaded and ready.");
        window.opencvReady = true;
        const pillStatus = document.getElementById('pillStatus');
        if (pillStatus && pillStatus.innerText === "Chargement OpenCV...") {
            pillStatus.innerText = "En attente";
            pillStatus.className = "status-badge status-en-attente";
        }
        // Trigger event for scanner
        document.dispatchEvent(new Event('opencv-ready'));
    }
    
    function onOpenCvError() {
        console.error("Failed to load OpenCV.js.");
        const pillStatus = document.getElementById('pillStatus');
        if (pillStatus) {
            pillStatus.innerText = "Erreur OpenCV";
            pillStatus.className = "status-badge status-corrige"; // visually red or distinct
        }
        alert("Impossible de charger OpenCV.js depuis le CDN. Le scanner risque de ne pas fonctionner.");
    }
</script>
<script async src="https://docs.opencv.org/4.5.5/opencv.js" onload="onOpenCvReady();" onerror="onOpenCvError();"></script>
<script src="<?= BASE_URL ?>/js/qcm-scanner.js"></script>