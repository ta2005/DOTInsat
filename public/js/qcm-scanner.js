// public/js/qcm-scanner.js
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

    // Drag and drop setup
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
        if (!logStream) {
            console.log(message);
            return;
        }
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        entry.innerText = `[${new Date().toLocaleTimeString()}] ${message}`;
        logStream.appendChild(entry);
        logStream.scrollTop = logStream.scrollHeight;
    }

    function processIncomingImageFile(file) {
        writeLog(`Loading image file: ${file.name}`);
        const reader = new FileReader();

        reader.onload = (event) => {
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

    async function executeOpticalBubbleAnalysis(imgSource) {
        pillStatus.innerText = "Processing Structure...";
        pillStatus.className = "status-badge status-en-attente";

        if (typeof cv === 'undefined' || !window.opencvReady) {
            writeLog("Waiting for OpenCV.js framework instantiation...");
            document.addEventListener('opencv-ready', () => { executeOpticalBubbleAnalysis(imgSource); }, { once: true });
            return;
        }

        const mockDetectedExamId = parseInt(document.getElementById('scanExamId').value) || 14;
        const realStudentIdEl = document.getElementById('scanRealStudentId');
        const mockDetectedStudentId = (realStudentIdEl && realStudentIdEl.value) ? parseInt(realStudentIdEl.value) : (parseInt(document.getElementById('scanStudentId').value) || 1002);

        // Runtime variables overwritten dynamically by database template specs
        let mockTotalQuestions = 10;
        let mockChoicesCount = 4;
        let cols = 3;

        try {
            const response = await fetch(`/api/qcm/get-template?exam_id=${mockDetectedExamId}`);
            const resParsed = await response.json();
            if (resParsed.success && resParsed.data) {
                mockTotalQuestions = resParsed.data.total_questions || 10;
                mockChoicesCount = resParsed.data.choices_per_question || 4;
                cols = resParsed.data.columns_count || 3;
                writeLog(`Template verified: ${mockTotalQuestions} Questions | ${mockChoicesCount} Choices | ${cols} Grid Columns`);
            }
        } catch (e) {
            writeLog("Warning: Using local layout fallback variables.");
        }

        lblStudent.innerText = `Student ID: ${mockDetectedStudentId} (Exam Context #${mockDetectedExamId})`;

        let src = cv.imread(canvas);
        let gray = new cv.Mat();
        let thresh = new cv.Mat();

        cv.cvtColor(src, gray, cv.COLOR_RGBA2GRAY);
        // Adaptive thresholding handles changes in lighting and shadows across the sheet
        cv.adaptiveThreshold(gray, thresh, 255, cv.ADAPTIVE_THRESH_GAUSSIAN_C, cv.THRESH_BINARY_INV, 21, 5);

        let contours = new cv.MatVector();
        let hierarchy = new cv.Mat();
        // RETR_CCOMP provides a 2-level structural topology map (Outer Ring vs Inner Content)
        cv.findContours(thresh, contours, hierarchy, cv.RETR_CCOMP, cv.CHAIN_APPROX_SIMPLE);

        let validBubbles = [];
        const hierarchyData = hierarchy.data32S;

        for (let i = 0; i < contours.size(); ++i) {
            let hIdx = i * 4;
            let nextElement = hierarchyData[hIdx];
            let previousElement = hierarchyData[hIdx + 1];
            let firstChild = hierarchyData[hIdx + 2];
            let parentElement = hierarchyData[hIdx + 3];

            let rect = cv.boundingRect(contours.get(i));
            let aspectRatio = rect.width / rect.height;

            // 1. DIMENSION CHECK: Filter out huge blocks (titles, headers) or speckle noise
            if (rect.width >= 16 && rect.width <= 70 && rect.height >= 16 && rect.height <= 70) {

                // 2. STRUCTURAL FILTERING (The Anti-Square Savior):
                // Valid empty bubbles have a child contour inside (the inner ring/letter).
                // Solid completely filled bubbles lose their child contour but are highly circular.
                let hasChild = firstChild !== -1;
                let isInsideParent = parentElement !== -1;

                if ((aspectRatio >= 0.65 && aspectRatio <= 1.45) && (!isInsideParent || hasChild)) {
                    // Check area fill density to differentiate actual bubbles from textual stray marks
                    let roi = thresh.roi(rect);
                    let nonZero = cv.countNonZero(roi);
                    let density = nonZero / (rect.width * rect.height);
                    roi.delete();

                    // Text letters like "U" or "a" show extremely low baseline densities (<15%) when inverted
                    if (density > 0.18) {
                        validBubbles.push({ rect, density });
                    }
                }
            }
        }

        writeLog(`Filtered out textual noise elements. Valid structural bubble candidates remaining: ${validBubbles.length}`);

        if (validBubbles.length < (mockTotalQuestions * 2)) {
            writeLog("Error: Structural signature tracking lost. Target bubbles missing.");
            pillStatus.innerText = "Scan Failed";
            gray.delete(); thresh.delete(); contours.delete(); hierarchy.delete(); src.delete();
            return;
        }

        // ---------------------------------------------------------
        // CLUSTER-BASED ADAPTIVE GRID ALIGNMENT
        // ---------------------------------------------------------
        // Cluster rows naturally by physical proximity (Y-coordinates)
        validBubbles.sort((a, b) => a.rect.y - b.rect.y);
        let horizontalRows = [];

        for (let bubble of validBubbles) {
            let assigned = false;
            for (let row of horizontalRows) {
                if (Math.abs(row[0].rect.y - bubble.rect.y) < (bubble.rect.height * 0.60)) {
                    row.push(bubble);
                    assigned = true;
                    break;
                }
            }
            if (!assigned) {
                horizontalRows.push([bubble]);
            }
        }

        // Clean up incomplete structural rows (stray artifacts or text lines captured accidentally)
        let expectedBubblesPerRow = cols * mockChoicesCount;
        horizontalRows = horizontalRows.filter(row => row.length >= (expectedBubblesPerRow * 0.75));

        // Sort individual rows from left to right
        horizontalRows.forEach(row => row.sort((a, b) => a.rect.x - b.rect.x));

        let compiledStudentAnswers = {};

        // Loop over the expected text question blocks
        for (let q = 1; q <= mockTotalQuestions; q++) {
            let colIdx = (q - 1) % cols;
            let rowIdx = Math.floor((q - 1) / cols);

            let targetRow = horizontalRows[rowIdx];
            if (!targetRow) {
                compiledStudentAnswers[`q${q}`] = "";
                continue;
            }

            // Isolate choices belonging explicitly to this column grouping
            let totalRowItems = targetRow.length;
            let approxItemsPerCol = Math.ceil(totalRowItems / cols);

            let colStartIdx = colIdx * approxItemsPerCol;
            let colEndIdx = Math.min(totalRowItems, colStartIdx + approxItemsPerCol);
            let questionChoices = targetRow.slice(colStartIdx, colEndIdx);

            // Re-verify sorting internally left-to-right
            questionChoices.sort((a, b) => a.rect.x - b.rect.x);

            // Map physical bubbles to option selections
            let choiceEvaluations = [];
            for (let c = 0; c < mockChoicesCount; c++) {
                let targetBubble = questionChoices[c];
                let currentLetter = alphabetOptions[c];

                if (!targetBubble) {
                    choiceEvaluations.push({ choice: currentLetter, density: 0, rect: null });
                    continue;
                }

                // Sample density using a tighter, centered region of interest
                let innerW = Math.round(targetBubble.rect.width * 0.65);
                let innerH = Math.round(targetBubble.rect.height * 0.65);
                let innerX = targetBubble.rect.x + Math.round((targetBubble.rect.width - innerW) / 2);
                let innerY = targetBubble.rect.y + Math.round((targetBubble.rect.height - innerH) / 2);

                let cropRect = new cv.Rect(innerX, innerY, innerW, innerH);
                let roi = thresh.roi(cropRect);
                let fillDensity = cv.countNonZero(roi) / (innerW * innerH);
                roi.delete();

                choiceEvaluations.push({
                    choice: currentLetter,
                    density: fillDensity,
                    rect: targetBubble.rect
                });
            }

            // Determine filled option
            choiceEvaluations.sort((a, b) => b.density - a.density);
            let topChoice = choiceEvaluations[0];
            let runnerUp = choiceEvaluations[1];

            let pickedAnswer = "";
            // If the density is >0.55, the bubble is solidly blacked out
            if (topChoice.density > 0.55 && topChoice.rect !== null) {
                // Handle manual student corrections / cross-outs safely
                if (runnerUp && runnerUp.density > 0.48 && (topChoice.density - runnerUp.density) < 0.15) {
                    pickedAnswer = "";
                    writeLog(`Q${q}: Correction markup anomaly flagged. Choice discarded.`);
                } else {
                    pickedAnswer = topChoice.choice;
                }
            } else {
                writeLog(`Q${q}: Blank/Unanswered.`);
            }

            compiledStudentAnswers[`q${q}`] = pickedAnswer;

            // Draw bounding validation boxes to our debug canvas element layer
            if (pickedAnswer && topChoice.rect) {
                let p1 = new cv.Point(topChoice.rect.x, topChoice.rect.y);
                let p2 = new cv.Point(topChoice.rect.x + topChoice.rect.width, topChoice.rect.y + topChoice.rect.height);
                cv.rectangle(src, p1, p2, new cv.Scalar(40, 167, 69, 255), 2); // Production Green
            }
        }

        cv.imshow(canvas, src);

        gray.delete(); thresh.delete(); contours.delete(); hierarchy.delete(); src.delete();
        writeLog("Optical OMR structural alignment parsing cycle executed successfully.");

        await dispatchGradesToControllerPipeline(mockDetectedExamId, mockDetectedStudentId, compiledStudentAnswers);
    }

    async function dispatchGradesToControllerPipeline(examId, studentId, answers) {
        writeLog(`Transmitting payload details to core engine: ${JSON.stringify(answers)}`);
        pillStatus.innerText = "Syncing Grades...";

        try {
            const response = await fetch('/api/qcm/submit-grade', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    exam_id: examId,
                    student_id: studentId,
                    answers: answers
                })
            });
            const res = await response.json();
            if (res.success) {
                pillStatus.innerText = "Scan Verified";
                pillStatus.className = "status-badge status-verifie";
                if (lblScore) lblScore.innerText = `${res.score} / ${res.total_possible}`;
            } else {
                pillStatus.innerText = "Submission Rejected";
                pillStatus.className = "status-badge status-conteste";
            }
        } catch (err) {
            writeLog(`API synchronization failure encountered: ${err.message}`);
            pillStatus.innerText = "Sync Error";
        }
    }
});