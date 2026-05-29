// Global flag for OpenCV readiness
window.opencvReady = false;

window.onOpenCvReady = function () {
    console.log("OpenCV.js is fully loaded and ready.");
    window.opencvReady = true;
    const pillStatus = document.getElementById('pillStatus');
    if (pillStatus && pillStatus.innerText === "Chargement OpenCV...") {
        pillStatus.innerText = "En attente";
        pillStatus.className = "status-badge status-en-attente";
    }
    // Trigger event for scanner
    document.dispatchEvent(new Event('opencv-ready'));
};

window.onOpenCvError = function () {
    console.error("Failed to load OpenCV.js.");
    const pillStatus = document.getElementById('pillStatus');
    if (pillStatus) {
        pillStatus.innerText = "Erreur OpenCV";
        pillStatus.className = "status-badge status-corrige"; // visually red or distinct
    }
    alert("Impossible de charger OpenCV.js depuis le CDN. Le scanner risque de ne pas fonctionner.");
};

document.addEventListener('DOMContentLoaded', () => {
    const examData = window.examData || [];
    const initialExamId = window.initialExamId || 0;
    const initialStudentId = window.initialStudentId || 0;
    const initialStudentCin = window.initialStudentCin || 0;

    const examSelect = document.getElementById('scanExamIdSelect');
    const studentSelect = document.getElementById('scanStudentIdSelect');

    const hiddenRealStudentId = document.getElementById('scanRealStudentId');
    const hiddenExamId = document.getElementById('scanExamId');
    const hiddenStudentId = document.getElementById('scanStudentId');

    if (examSelect) {
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
    }

    if (studentSelect) {
        // Event listener: when student selection changes
        studentSelect.addEventListener('change', () => {
            const selectedStudentId = parseInt(studentSelect.value) || 0;
            hiddenRealStudentId.value = selectedStudentId || '';

            const selectedOption = studentSelect.options[studentSelect.selectedIndex];
            const selectedCin = selectedOption ? (selectedOption.getAttribute('data-cin') || '') : '';
            hiddenStudentId.value = selectedCin;
        });
    }

    // Auto-select initial state if navigated from the dashboard
    if (examSelect && initialExamId > 0) {
        examSelect.value = initialExamId;
        examSelect.dispatchEvent(new Event('change'));

        if (studentSelect && initialStudentId > 0) {
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

// public/js/qcm-scanner.js — OMR
document.addEventListener('DOMContentLoaded', () => {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileFallbackInput');
    const canvas = document.getElementById('processingCanvas');
    const ctx = canvas.getContext('2d');

    const logStream = document.getElementById('logStream');
    const lblStudent = document.getElementById('lblSessionStudent');
    const pillStatus = document.getElementById('pillStatus');
    const lblScore = document.getElementById('lblCalculatedScore');

    const alphabetOptions = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

    // ── Drag & Drop Handlers ───────────────────────────────────
    ['dragenter', 'dragover'].forEach(name =>
        dropzone.addEventListener(name, e => { e.preventDefault(); dropzone.classList.add('dragover'); }, false)
    );
    ['dragleave', 'drop'].forEach(name =>
        dropzone.addEventListener(name, e => { e.preventDefault(); dropzone.classList.remove('dragover'); }, false)
    );
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length > 0) processIncomingImageFile(e.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', e => {
        if (e.target.files.length > 0) processIncomingImageFile(e.target.files[0]);
    });

    // ── Logging System ─────────────────────────────────────────
    function writeLog(message) {
        if (!logStream) { console.log(message); return; }
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        entry.innerText = `[${new Date().toLocaleTimeString()}] ${message}`;
        logStream.appendChild(entry);
        logStream.scrollTop = logStream.scrollHeight;
    }

    // ── Image Preloading ───────────────────────────────────────
    function processIncomingImageFile(file) {
        writeLog(`Loading image file: ${file.name}`);
        const reader = new FileReader();
        reader.onload = event => {
            const img = new Image();
            img.onload = () => {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                canvas.style.display = 'block';
                executeOpticalBubbleAnalysis(img);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    // ══════════════════════════════════════════════════════════
    //  CORE OMR ENGINE
    // ══════════════════════════════════════════════════════════
    async function executeOpticalBubbleAnalysis(imgSource) {
        pillStatus.innerText = 'Processing Structure…';
        pillStatus.className = 'status-badge status-en-attente';

        if (typeof cv === 'undefined' || !window.opencvReady) {
            writeLog('Waiting for OpenCV.js framework instantiation…');
            document.addEventListener('opencv-ready', () => executeOpticalBubbleAnalysis(imgSource), { once: true });
            return;
        }

        // ── Fetch Dynamic Template Configurations ───────────────
        const mockDetectedExamId = parseInt(document.getElementById('scanExamId').value) || 14;
        const realStudentIdEl = document.getElementById('scanRealStudentId');
        const mockDetectedStudentId = (realStudentIdEl?.value)
            ? parseInt(realStudentIdEl.value)
            : (parseInt(document.getElementById('scanStudentId').value) || 1002);

        let mockTotalQuestions = 10;
        let mockChoicesCount = 4;
        let cols = 3;

        try {
            const response = await fetch(`/?page=api-get-template&exam_id=${mockDetectedExamId}`);
            const resParsed = await response.json();
            if (resParsed.success && resParsed.data) {
                mockTotalQuestions = resParsed.data.total_questions || 10;
                mockChoicesCount = resParsed.data.choices_per_question || 4;
                cols = resParsed.data.columns_count || 3;
                writeLog(`Template Connected: ${mockTotalQuestions}Q | ${mockChoicesCount} choices | ${cols} columns Layout.`);
            }
        } catch {
            writeLog('Warning: API unreachable. Using standard local template values.');
        }

        lblStudent.innerText = `Student ID: ${mockDetectedStudentId} (Exam #${mockDetectedExamId})`;

        let src = cv.imread(canvas);
        let gray = new cv.Mat();
        let blurred = new cv.Mat();
        let thresh = new cv.Mat();

        // isolation filtering
        cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
        cv.GaussianBlur(gray, blurred, new cv.Size(5, 5), 0);
        cv.adaptiveThreshold(blurred, thresh, 255, cv.ADAPTIVE_THRESH_GAUSSIAN_C, cv.THRESH_BINARY_INV, 25, 7);

        let contours = new cv.MatVector();
        let hierarchy = new cv.Mat();
        cv.findContours(thresh, contours, hierarchy, cv.RETR_CCOMP, cv.CHAIN_APPROX_SIMPLE);

        let pristineCircles = [];
        const hData = hierarchy.data32S;

        // ── Step 1: Detect Untouched Circles ──────────────────────
        for (let i = 0; i < contours.size(); ++i) {
            const rect = cv.boundingRect(contours.get(i));
            const aspectRatio = rect.width / rect.height;

            if (rect.y < src.rows * 0.22) continue; // Skip header area
            if (rect.width < 14 || rect.width > 75 || rect.height < 14 || rect.height > 75) continue;

            const area = cv.contourArea(contours.get(i));
            const perimeter = cv.arcLength(contours.get(i), true);
            const circularity = perimeter > 0 ? (4 * Math.PI * area) / (perimeter * perimeter) : 0;

            if (circularity < 0.45) continue;
            if (aspectRatio < 0.70 || aspectRatio > 1.30) continue;

            const roi = thresh.roi(rect);
            const density = cv.countNonZero(roi) / (rect.width * rect.height);
            roi.delete();
            if (density < 0.16) continue;

            pristineCircles.push({ rect });
        }

        if (pristineCircles.length < 4) {
            writeLog(`Error: Clean structural signature lost.`);
            pillStatus.innerText = 'Scan Failed';
            cleanup(gray, blurred, thresh, contours, hierarchy, src);
            return;
        }

        // ── Step 2: Calculate Sheet Matrix Boundaries ────────────
        let allX = pristineCircles.map(b => b.rect.x).sort((a, b) => a - b);
        let minGridX = allX[Math.floor(allX.length * 0.01)];
        let maxGridX = allX[Math.floor(allX.length * 0.99)] + (pristineCircles[0].rect.width);

        let totalGridW = maxGridX - minGridX;
        let columnBlockWidth = totalGridW / cols;

        // ── Step 3: CRITICAL FIX - Build Global Column Anchors ──
        // This calculates the horizontal positions using all bubbles on the page at once
        const globalColumnAnchors = [];
        for (let cIdx = 0; cIdx < cols; cIdx++) {
            let colLeftBoundary = minGridX + (cIdx * columnBlockWidth);
            let colRightBoundary = colLeftBoundary + columnBlockWidth;

            // Gather all structural bubbles across all rows falling into this column group
            let colCircles = pristineCircles.filter(b => {
                let midX = b.rect.x + b.rect.width / 2;
                return midX >= colLeftBoundary && midX <= colRightBoundary;
            });

            if (colCircles.length > 0) {
                let colXPositions = colCircles.map(b => b.rect.x).sort((a, b) => a - b);
                let colMinX = colXPositions[0];
                let colMaxX = colXPositions[colXPositions.length - 1];

                // Calculate step distance between option bubbles (A -> B -> C -> D)
                let calculatedStep = (colCircles.length > 1)
                    ? (colMaxX - colMinX) / (mockChoicesCount - 1)
                    : pristineCircles[0].rect.width * 1.50;

                globalColumnAnchors[cIdx] = { minX: colMinX, step: calculatedStep };
            } else {
                // Safe math fallback fallback if a column block is completely empty
                globalColumnAnchors[cIdx] = {
                    minX: colLeftBoundary + (columnBlockWidth * 0.28),
                    step: pristineCircles[0].rect.width * 1.50
                };
            }
        }

        // ── Step 4: Vertical Row Clustering ───────────────────────
        pristineCircles.sort((a, b) => a.rect.y - b.rect.y);
        let structuredHorizontalRows = [];

        for (const bubble of pristineCircles) {
            const bCenterY = bubble.rect.y + bubble.rect.height / 2;
            let assigned = false;

            for (const row of structuredHorizontalRows) {
                const rowAvgY = row.reduce((sum, b) => sum + b.rect.y + b.rect.height / 2, 0) / row.length;
                const tolerance = bubble.rect.height * 0.70;
                if (Math.abs(rowAvgY - bCenterY) < tolerance) {
                    row.push(bubble);
                    assigned = true;
                    break;
                }
            }
            if (!assigned) structuredHorizontalRows.push([bubble]);
        }

        structuredHorizontalRows = structuredHorizontalRows.filter(r => r.length >= 1);
        structuredHorizontalRows.sort((a, b) => {
            const avgA = a.reduce((s, x) => s + x.rect.y, 0) / a.length;
            const avgB = b.reduce((s, x) => s + x.rect.y, 0) / b.length;
            return avgA - avgB;
        });

        // ── Step 5: Extraction Matrix Execution ──────────────────
        const compiledStudentAnswers = {};
        const globalBubbleW = pristineCircles[0].rect.width;
        const globalBubbleH = pristineCircles[0].rect.height;

        for (let rowIdx = 0; rowIdx < structuredHorizontalRows.length; rowIdx++) {
            const targetLane = structuredHorizontalRows[rowIdx];
            const laneCenterY = targetLane.reduce((s, b) => s + b.rect.y + b.rect.height / 2, 0) / targetLane.length;

            for (let colIdx = 0; colIdx < cols; colIdx++) {
                const q = rowIdx * cols + colIdx + 1;
                if (q > mockTotalQuestions) break;

                // Load calibrated un-shakable column layout coordinates
                const anchorData = globalColumnAnchors[colIdx];
                const choiceEvaluations = [];

                for (let c = 0; c < mockChoicesCount; c++) {
                    const currentLetter = alphabetOptions[c];
                    let computedTargetX = anchorData.minX + (c * anchorData.step);

                    let virtualRect = {
                        x: Math.round(computedTargetX),
                        y: Math.round(laneCenterY - globalBubbleH / 2),
                        width: Math.round(globalBubbleW),
                        height: Math.round(globalBubbleH)
                    };

                    // High-density regional profiling window
                    const innerW = Math.round(virtualRect.width * 0.82);
                    const innerH = Math.round(virtualRect.height * 0.82);
                    const innerX = Math.max(0, Math.min(gray.cols - innerW - 1, virtualRect.x + Math.round((virtualRect.width - innerW) / 2)));
                    const innerY = Math.max(0, Math.min(gray.rows - innerH - 1, virtualRect.y + Math.round((virtualRect.height - innerH) / 2)));

                    // 1. Core Interior Darkness Value
                    const roiTarget = gray.roi(new cv.Rect(innerX, innerY, innerW, innerH));
                    const meanInteriorPixel = cv.mean(roiTarget)[0];
                    roiTarget.delete();

                    // 2. Local White Paper Reference Value
                    const bgSamplingH = Math.round(virtualRect.height * 0.25);
                    const bgSampleY = Math.max(0, virtualRect.y - bgSamplingH - 4);
                    const roiBackground = gray.roi(new cv.Rect(virtualRect.x, bgSampleY, virtualRect.width, bgSamplingH));
                    const meanBackgroundPixel = roiBackground.size().width > 0 && roiBackground.size().height > 0 ? cv.mean(roiBackground)[0] : 255;
                    roiBackground.delete();

                    let contrastFillRatio = 0.0;
                    if (meanBackgroundPixel > 0) {
                        contrastFillRatio = (meanBackgroundPixel - meanInteriorPixel) / meanBackgroundPixel;
                    }
                    contrastFillRatio = Math.max(0.0, contrastFillRatio);

                    choiceEvaluations.push({
                        choice: currentLetter,
                        density: contrastFillRatio,
                        rect: virtualRect
                    });
                }

                // Adaptive Marking Threshold Evaluator
                const densities = choiceEvaluations.map(c => c.density);
                const maxDensity = Math.max(...densities);
                const avgDensity = densities.reduce((a, b) => a + b, 0) / densities.length;

                const questionAdaptiveThresh = Math.max(0.18, avgDensity + (maxDensity - avgDensity) * 0.40);

                choiceEvaluations.sort((a, b) => b.density - a.density);
                const topChoice = choiceEvaluations[0];
                const runnerUp = choiceEvaluations[1];

                let selectedAnswer = '';

                if (topChoice.density >= questionAdaptiveThresh && topChoice.density >= 0.14) {
                    const isAmbiguous = runnerUp && runnerUp.density > 0.16 && (topChoice.density - runnerUp.density) < 0.12;
                    if (isAmbiguous) {
                        writeLog(`Q${q}: Ambiguous double choice detected.`);
                    } else {
                        selectedAnswer = topChoice.choice;
                    }
                }

                compiledStudentAnswers[`q${q}`] = selectedAnswer;

                // Draw alignment validation boxes
                if (selectedAnswer && topChoice.rect) {
                    cv.rectangle(src,
                        new cv.Point(topChoice.rect.x, topChoice.rect.y),
                        new cv.Point(topChoice.rect.x + topChoice.rect.width, topChoice.rect.y + topChoice.rect.height),
                        new cv.Scalar(40, 167, 69, 255), 2);
                }
            }
        }

        cv.imshow(canvas, src);
        cleanup(gray, blurred, thresh, contours, hierarchy, src);

        writeLog('OMR grid matrix calculation completed perfectly.');
        await dispatchGradesToControllerPipeline(mockDetectedExamId, mockDetectedStudentId, compiledStudentAnswers);
    }

    function cleanup(...mats) {
        for (const m of mats) { try { m.delete(); } catch { } }
    }

    async function dispatchGradesToControllerPipeline(examId, studentId, answers) {
        writeLog(`Transmitting answers matrix: ${JSON.stringify(answers)}`);
        pillStatus.innerText = 'Syncing Grades…';

        try {
            const response = await fetch('/?page=api-process-scan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ exam_id: examId, student_id: studentId, student_answers: answers })
            });
            const res = await response.json();
            if (res.success) {
                pillStatus.innerText = 'Synchronisé avec succès';
                pillStatus.className = 'status-badge status-corrige';
                if (lblScore) lblScore.innerText = parseFloat(res.data.final_grade).toFixed(2);
                writeLog(`[Success] Processing Complete. Verified Score: ${res.data.final_grade} pts`);
            } else {
                pillStatus.innerText = 'Échec Synchro';
                pillStatus.className = 'status-badge status-en-attente';
                writeLog(`[Error] Engine Refusal: ${res.message}`);
            }
        } catch (err) {
            writeLog(`Critical API network failure occurred: ${err.message}`);
            pillStatus.innerText = 'Sync Error';
        }
    }
});