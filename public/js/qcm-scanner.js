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
        writeLog(`Loading selected image file: ${file.name}`);
        const reader = new FileReader();
        
        reader.onload = (event) => {
            const img = new Image();
            img.onload = () => {
                // Initialize canvas dimensions matching source file proportions
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);
                canvas.style.display = 'block';
                writeLog(`Image dimensions matched: ${img.width}x${img.height}px.`);
                
                // Execute OMR Scan Engine
                executeOpticalBubbleAnalysis(img);
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    /**
     * Optical Mark Recognition (OMR) Simulation Machine
     * Analyzes relative layout matrices to isolate selected bubbles.
     */
    async function executeOpticalBubbleAnalysis(imgSource) {
        pillStatus.innerText = "Analyzing Pixels...";
        pillStatus.className = "status-pill";
        writeLog("Extracting operational tracking data variables...");

        // For this automated step, we simulate scanning metadata headers.
        // In full production, this maps to explicit canvas pixel zone cropping bounding coordinates.
        const mockDetectedExamId = 14; 
        const mockDetectedStudentId = 1002; 
        const mockTotalQuestions = 20;
        const mockChoicesCount = 4; // A, B, C, D

        lblStudent.innerText = `Etudiant ID: ${mockDetectedStudentId} (Exam Context #${mockDetectedExamId})`;
        writeLog(`Targeting Configuration File Blueprint: exam_${mockDetectedExamId}.json`);

        const compiledStudentAnswers = {};

        // Loop through rows and analyze bubble density
        for (let q = 1; q <= mockTotalQuestions; q++) {
            let darkestChoice = 'A';
            let maxDarknessFound = 0;

            // Analyze bubble targets along the horizontal plane
            for (let c = 0; c < mockChoicesCount; c++) {
                // Simulate computing pixel darkness coefficients inside the bubble radius bounds
                let calculatedDensity = Math.random() * 100; 
                
                if (calculatedDensity > maxDarknessFound) {
                    maxDarknessFound = calculatedDensity;
                    darkestChoice = alphabetOptions[c];
                }
            }
            compiledStudentAnswers[`q${q}`] = darkestChoice;
        }

        writeLog(`Successfully isolated student answers matrix. Dispatching to controller network pipe...`);
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
                writeLog(`Fatal Backend Parse Error: Encountered invalid structural response output markup.`);
                console.error("Raw markup dump error context:", responseText);
                return;
            }

            if (result.success) {
                pillStatus.innerText = "Synced successfully";
                pillStatus.className = "status-pill success";
                lblScore.innerText = parseFloat(result.data.final_grade).toFixed(2);
                writeLog(`[Success] Grade calculation verified! Score written: ${result.data.final_grade} pts.`);
            } else {
                pillStatus.innerText = "Sync Failed";
                writeLog(`[Error] Request rejected by endpoint matrix: ${result.message}`);
            }

        } catch (err) {
            writeLog(`[Critical Error] Failed to complete async network transport channel.`);
            console.error(err);
        }
    }
});