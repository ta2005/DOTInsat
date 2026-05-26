// public/js/qcm-scanner.js
document.addEventListener('DOMContentLoaded', () => {
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileFallbackInput');
    const canvas = document.getElementById('processingCanvas');
    const ctx = canvas.getContext('2d');
    
    // UI Logging elements
    const logStream = document.getElementById('logStream');
    const lblStudent = document.getElementById('lblSessionStudent');
    const pillStatus = document.getElementById('pillStatus');
    const lblScore = document.getElementById('lblCalculatedScore');

    const alphabetOptions = ['A', 'B', 'C', 'D', 'E'];

    // Drag and drop event listeners
    ['dragenter', 'dragover'].forEach(name => {
        dropzone.addEventListener(name, (e) => { e.preventDefault(); dropzone.classList.add('dragover'); }, false);
    });
    ['dragleave', 'drop'].forEach(name => {
        dropzone.addEventListener(name, (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const files = e.dataTransfer.files;
        if (files.length > 0) processIncomingImageFile(files[0]);
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) processIncomingImageFile(e.target.files[0]);
    });

    function writeLog(message) {
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        entry.innerText = `[${new Date().toLocaleTimeString()}] ${message}`;
        logStream.appendChild(entry);
        logStream.scrollTop = logStream.scrollHeight;
    }

    function processIncomingImageFile(file) {
        writeLog(`Chargement du fichier image sélectionné : ${file.name}`);
        const reader = new FileReader();
        
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                // Initialize canvas dimensions matching source file proportions
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                canvas.style.display = 'block';
                writeLog(`Dimensions de l'image correspond : ${img.width}x${img.height}px.`);
                
                // Execute OMR Scan Engine
                executeOpticalBubbleAnalysis(img);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    /**
     * Optical Mark Recognition (OMR) Scan Machine
     * Analyzes relative layout matrices to isolate selected bubbles.
     */
    async function executeOpticalBubbleAnalysis(imgSource) {
        pillStatus.innerText = "Analyse des Pixels...";
        pillStatus.className = "status-pill";
        writeLog("Extraction des variables de données de suivi opérationnel...");

        const mockDetectedExamId = parseInt(document.getElementById('scanExamId').value) || 14; 
        const mockDetectedStudentId = parseInt(document.getElementById('scanStudentId').value) || 1002; 
        
        let mockTotalQuestions = 10;
        let mockChoicesCount = 2; // A, B

        // First, fetch template context to get exact question count and choices per question
        try {
            writeLog("Récupération des configurations de modèles pour étalonner les dimensions de la grille...");
            const response = await fetch(`/api/qcm/get-template?exam_id=${mockDetectedExamId}`);
            const resParsed = await response.json();
            if (resParsed.success && resParsed.data) {
                mockTotalQuestions = resParsed.data.total_questions || 10;
                mockChoicesCount = resParsed.data.choices_per_question || 2;
                writeLog(`Étalonnage de grille actif : ${mockTotalQuestions} questions, ${mockChoicesCount} choix par question.`);
            }
        } catch (e) {
            writeLog("Avertissement : Impossible de récupérer la configuration du modèle. Étalonnage avec les valeurs par défaut standard.");
        }

        lblStudent.innerText = `Etudiant ID: ${mockDetectedStudentId} (Exam Context #${mockDetectedExamId})`;
        writeLog(`Fichier de configuration cible Blueprint : exam_${mockDetectedExamId}.json`);

        const compiledStudentAnswers = {};
        writeLog("Exécution du véritable scanner de reconnaissance optique des marques (OMR) de pixels...");

        const W = canvas.width;
        const H = canvas.height;
        const imgData = ctx.getImageData(0, 0, W, H);
        const pixels = imgData.data;

        // Exclude edges (2% margin) to avoid scan borders
        const padX = Math.floor(W * 0.02);
        const padY = Math.floor(H * 0.02);

        // Precompute binary isDark array
        const isDark = new Uint8Array(W * H);
        for (let i = 0; i < W * H; i++) {
            const idx = i * 4;
            if (pixels[idx] < 180 && pixels[idx+1] < 180 && pixels[idx+2] < 180) {
                isDark[i] = 1;
            }
        }

        // 1. Horizontal projection (find row bands)
        const rowDarkness = new Int32Array(H);
        for (let y = padY; y < H - padY; y++) {
            let count = 0;
            for (let x = padX; x < W - padX; x++) {
                if (isDark[y * W + x]) {
                    count++;
                }
            }
            rowDarkness[y] = count;
        }

        const rowThreshold = Math.max(5, W * 0.005);
        const rawIntervals = [];
        let inInterval = false;
        let startY = 0;
        for (let y = padY; y < H - padY; y++) {
            const isActive = rowDarkness[y] > rowThreshold;
            if (isActive && !inInterval) {
                inInterval = true;
                startY = y;
            } else if (!isActive && inInterval) {
                inInterval = false;
                rawIntervals.push({ start: startY, end: y - 1 });
            }
        }
        if (inInterval) {
            rawIntervals.push({ start: startY, end: H - padY - 1 });
        }

        const filteredIntervals = rawIntervals.filter(interval => (interval.end - interval.start + 1) >= 5);

        const cols = 3;
        const numRows = Math.ceil(mockTotalQuestions / cols);
        
        // Take the bottom-most numRows intervals
        filteredIntervals.sort((a, b) => b.start - a.start);
        const detectedRowIntervals = filteredIntervals.slice(0, numRows);
        detectedRowIntervals.sort((a, b) => a.start - b.start);

        let rowIntervals = [];
        let colIntervals = [];
        let fallbackMode = false;

        // 2. Vertical projection (find column bands within rows)
        if (detectedRowIntervals.length === numRows) {
            rowIntervals = detectedRowIntervals;
            
            const colDarkness = new Int32Array(W);
            for (let x = padX; x < W - padX; x++) {
                let count = 0;
                for (const row of rowIntervals) {
                    for (let y = row.start; y <= row.end; y++) {
                        if (isDark[y * W + x]) {
                            count++;
                        }
                    }
                }
                colDarkness[x] = count;
            }

            // Smooth colDarkness using a moving average window to bridge gaps between labels and bubbles
            const smoothWindow = 35;
            const smoothedColDarkness = new Float32Array(W);
            for (let x = padX; x < W - padX; x++) {
                let sum = 0;
                let count = 0;
                for (let wx = x - Math.floor(smoothWindow/2); wx <= x + Math.floor(smoothWindow/2); wx++) {
                    if (wx >= padX && wx < W - padX) {
                        sum += colDarkness[wx];
                        count++;
                    }
                }
                smoothedColDarkness[x] = sum / count;
            }

            const colThreshold = Math.max(2, H * 0.002);
            const rawColIntervals = [];
            let inColInterval = false;
            let startX = 0;
            for (let x = padX; x < W - padX; x++) {
                const isActive = smoothedColDarkness[x] > colThreshold;
                if (isActive && !inColInterval) {
                    inColInterval = true;
                    startX = x;
                } else if (!isActive && inColInterval) {
                    inColInterval = false;
                    rawColIntervals.push({ start: startX, end: x - 1 });
                }
            }
            if (inColInterval) {
                rawColIntervals.push({ start: startX, end: W - padX - 1 });
            }

            const filteredColIntervals = rawColIntervals.filter(col => (col.end - col.start + 1) >= 20);
            filteredColIntervals.sort((a, b) => (b.end - b.start) - (a.end - a.start));
            
            const detectedColIntervals = filteredColIntervals.slice(0, cols);
            detectedColIntervals.sort((a, b) => a.start - b.start);

            if (detectedColIntervals.length === cols) {
                colIntervals = detectedColIntervals;
            } else {
                fallbackMode = true;
            }
        } else {
            fallbackMode = true;
        }

        if (fallbackMode) {
            writeLog(`Avertissement : L'étalonnage de grille n'a pas pu résoudre tous les bandes. Basculement au calcul de grille uniforme.`);
            let minX = W, minY = H, maxX = 0, maxY = 0;
            let foundAny = false;
            for (let y = padY; y < H - padY; y++) {
                for (let x = padX; x < W - padX; x++) {
                    if (isDark[y * W + x]) {
                        if (x < minX) minX = x;
                        if (y < minY) minY = y;
                        if (x > maxX) maxX = x;
                        if (y > maxY) maxY = y;
                        foundAny = true;
                    }
                }
            }
            if (!foundAny) {
                writeLog("Erreur : Impossible de localiser les lignes de bordure active de la feuille. Assurez-vous que l'image est à contraste élevé.");
                pillStatus.innerText = "Échec de l'analyse";
                return;
            }
            minX = Math.max(0, minX - 10);
            minY = Math.max(0, minY - 10);
            maxX = Math.min(W - 1, maxX + 10);
            maxY = Math.min(H - 1, maxY + 10);

            const contentW = maxX - minX;
            const contentH = maxY - minY;

            // Highlight content box in green
            ctx.strokeStyle = '#28a745';
            ctx.lineWidth = 3;
            ctx.strokeRect(minX, minY, contentW, contentH);

            const colW = contentW / cols;
            const rowH = contentH / numRows;

            rowIntervals = [];
            for (let r = 0; r < numRows; r++) {
                rowIntervals.push({
                    start: Math.floor(minY + r * rowH),
                    end: Math.ceil(minY + (r + 1) * rowH)
                });
            }
            colIntervals = [];
            for (let c = 0; c < cols; c++) {
                colIntervals.push({
                    start: Math.floor(minX + c * colW),
                    end: Math.ceil(minX + (c + 1) * colW)
                });
            }
        } else {
            writeLog(`Limites de grille OMR actives résolues : ${rowIntervals.length} bandes de lignes, ${colIntervals.length} bandes de colonnes.`);
            // Highlight detected grid boundaries in green
            ctx.strokeStyle = '#28a745';
            ctx.lineWidth = 2;
            for (const r of rowIntervals) {
                for (const c of colIntervals) {
                    ctx.strokeRect(c.start, r.start, c.end - c.start, r.end - r.start);
                }
            }
        }

        // 3. Scan each question cell inside grid intervals
        for (let q = 1; q <= mockTotalQuestions; q++) {
            const colIdx = (q - 1) % cols;
            const rowIdx = Math.floor((q - 1) / cols);

            const rowInterval = rowIntervals[rowIdx];
            const colInterval = colIntervals[colIdx];

            const cellX1 = colInterval.start;
            const cellX2 = colInterval.end;
            const cellY1 = rowInterval.start;
            const cellY2 = rowInterval.end;

            const cellW = cellX2 - cellX1;
            const cellH = cellY2 - cellY1;

            // Draw cell boundary debug line
            ctx.strokeStyle = 'rgba(0, 123, 255, 0.2)';
            ctx.lineWidth = 1;
            ctx.strokeRect(cellX1, cellY1, cellW, cellH);

            // Calculate start ratio of bubbles relative to question text label width
            // Derived from CSS: label width = 45px, bubble = 26px, gap = 18px
            const totalContentWidth = 45 + mockChoicesCount * 26 + (mockChoicesCount - 1) * 18;
            const startRatio = 45 / totalContentWidth;

            const bubbleRegionX1 = cellX1 + cellW * startRatio;
            const bubbleRegionW = cellW * (1 - startRatio);
            const segmentW = bubbleRegionW / mockChoicesCount;

            const choiceResults = [];

            for (let c = 0; c < mockChoicesCount; c++) {
                const choiceX1 = bubbleRegionX1 + c * segmentW;
                const choiceX2 = choiceX1 + segmentW;

                // Focus scanning bounds slightly inside the bubble region to avoid border artifacts
                const scanX1 = choiceX1 + segmentW * 0.15;
                const scanX2 = choiceX2 - segmentW * 0.15;
                const scanY1 = cellY1 + cellH * 0.15;
                const scanY2 = cellY2 - cellH * 0.15;

                const scanW = scanX2 - scanX1;
                const scanH = scanY2 - scanY1;
                const totalScanPixels = Math.max(1, scanW * scanH);

                let darkPixelCount = 0;
                for (let sy = Math.floor(scanY1); sy < Math.ceil(scanY2); sy++) {
                    for (let sx = Math.floor(scanX1); sx < Math.ceil(scanX2); sx++) {
                        if (sx >= 0 && sx < W && sy >= 0 && sy < H) {
                            if (isDark[sy * W + sx]) {
                                darkPixelCount++;
                            }
                        }
                    }
                }

                const darkRatio = darkPixelCount / totalScanPixels;
                choiceResults.push({
                    choice: alphabetOptions[c],
                    darkRatio: darkRatio,
                    scanX1: scanX1,
                    scanY1: scanY1,
                    scanW: scanW,
                    scanH: scanH
                });

                // Visualize scanning bucket
                ctx.strokeStyle = 'rgba(255, 193, 7, 0.3)';
                ctx.lineWidth = 1;
                ctx.strokeRect(scanX1, scanY1, scanW, scanH);
            }

            // Determine the selected choice by finding the one with maximum darkRatio
            let bestChoice = choiceResults[0];
            for (let c = 1; c < mockChoicesCount; c++) {
                if (choiceResults[c].darkRatio > bestChoice.darkRatio) {
                    bestChoice = choiceResults[c];
                }
            }

            const selectedChoice = bestChoice.choice;
            compiledStudentAnswers[`q${q}`] = selectedChoice;

            // Draw visual confirmation around chosen bubble
            ctx.strokeStyle = '#28a745';
            ctx.lineWidth = 2;
            ctx.strokeRect(bestChoice.scanX1, bestChoice.scanY1, bestChoice.scanW, bestChoice.scanH);

            // Print overlay text showing detected choice
            ctx.fillStyle = '#28a745';
            ctx.font = 'bold 12px monospace';
            ctx.fillText(selectedChoice, bestChoice.scanX1 + bestChoice.scanW / 2 - 4, bestChoice.scanY1 - 2);
        }

        writeLog("Le scanner OMR a calculé avec succès les réponses à partir des profils de densité de cercles!");

        writeLog(`Matrice de réponses étudiantes isolée avec succès. Envoi vers le canal de conduit réseau du contrôleur...`);
        await dispatchGradesToControllerPipeline(mockDetectedExamId, mockDetectedStudentId, compiledStudentAnswers);
    }

    /**
     * Sends the compiled results matrix to the backend API endpoint
     */
    async function dispatchGradesToControllerPipeline(examId, studentId, studentAnswers) {
        try {
            const endpointUrl = '/api/qcm/process-scan';
            const payload = {
                exam_id: examId,
                student_id: studentId,
                student_answers: studentAnswers
            };

            const response = await fetch(endpointUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const responseText = await response.text();
            let result;
            
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                writeLog(`Erreur d'Analyse Backend Fatale : Balisage de sortie de réponse structurelle invalide rencontré.`);
                console.error("Raw markup dump error context:", responseText);
                return;
            }

            if (result.success) {
                pillStatus.innerText = "Synchronisé avec succès";
                pillStatus.className = "status-pill success";
                lblScore.innerText = parseFloat(result.data.final_grade).toFixed(2);
                writeLog(`[Succès] Calcul de note vérifié ! Note écrite : ${result.data.final_grade} pts.`);
            } else {
                pillStatus.innerText = "Échec de la synchronisation";
                writeLog(`[Erreur] Demande rejetée par la matrice de point d'accès : ${result.message}`);
            }

        } catch (err) {
            writeLog(`[Erreur Critique] Impossible de terminer le canal de transport réseau asynchrone.`);
            console.error(err);
        }
    }
});