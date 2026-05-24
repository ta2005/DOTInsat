<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Professor Panel - Create QCM Master Template</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        /* Scoped styles for the Professor QCM Generator Engine */
        .config-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .setup-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .input-wrapper {
            flex: 1;
            min-width: 180px;
            display: flex;
            flex-direction: column;
        }

        .input-wrapper label {
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 0.9rem;
            color: #495057;
        }

        .input-wrapper input,
        .input-wrapper select {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }

        /* Grid Layout for Interactive Matrix Mapping Keys */
        #interactiveMatrixWorkspace {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .matrix-item {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s;
        }

        .matrix-item:hover {
            transform: translateY(-2px);
            border-color: #b5b8bd;
        }

        .matrix-header {
            margin: 0 0 12px 0;
            color: #212529;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 8px;
        }

        .choice-row {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-top: 5px;
        }

        .bubble-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-weight: 600;
            font-family: monospace;
            cursor: pointer;
            font-size: 1.1rem;
        }

        /* Master Copy Printable Document Styling Layout */
        #printableBubbleDocument {
            display: none;
            background: #fff;
            padding: 50px;
            border: 2px dashed #6c757d;
            border-radius: 6px;
            margin-top: 40px;
        }

        .doc-title-block {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 4px double #000;
            padding-bottom: 20px;
        }

        .doc-title-block h1 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            letter-spacing: 1px;
        }

        .student-info-grid {
            border: 2px solid #000;
            padding: 20px;
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
            background: #fafafa;
        }

        .student-info-grid p {
            margin: 10px 0;
            font-size: 1.05rem;
        }

        .sheet-columns-wrapper {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .sheet-row-line {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-family: monospace;
            font-size: 1.15rem;
        }

        .sheet-question-num {
            width: 45px;
            font-weight: bold;
            color: #000;
        }

        .sheet-bubbles-container {
            display: flex;
            gap: 18px;
        }

        .vector-bubble-circle {
            width: 26px;
            height: 26px;
            border: 2px solid #000;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            color: #333;
            background: #fff;
        }

        /* Actions styling */
        .control-actions {
            margin-top: 25px;
            display: flex;
            gap: 15px;
            background: #fff;
            padding: 15px 0;
            position: sticky;
            bottom: 0;
            border-top: 1px solid #dee2e6;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #0d6efd;
            color: white;
        }

        .btn-success {
            background-color: #198754;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        /* Print Override Layout Configuration Interceptor */
        @media print {
            body * {
                visibility: hidden;
            }

            #printableBubbleDocument,
            #printableBubbleDocument * {
                visibility: visible;
            }

            #printableBubbleDocument {
                display: block !important;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                padding: 0;
                margin: 0;
            }

            .no-print-layer {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="container no-print-layer" style="padding: 30px; max-width: 1200px; margin: 0 auto;">
        <h2>Create New Automated QCM Pattern Key</h2>
        <p class="text-muted">Set up structural parameters for your evaluation sheet. The engine compiles an interactive
            answers matrix and outputs a custom grid blueprint matching your configurations.</p>

        <div class="config-card">
            <div class="setup-row">
                <div class="input-wrapper">
                    <label for="controleId">Exam Context Association (PostgreSQL ID)</label>
                    <input type="number" id="controleId" min="1" placeholder="e.g., 14" required>
                </div>
                <div class="input-wrapper">
                    <label for="totalQuestions">Number of Questions (10 - 40)</label>
                    <input type="number" id="totalQuestions" min="10" max="40" value="20" required>
                </div>
                <div class="input-wrapper">
                    <label for="choicesPerQuestion">Options Per Question (2 - 5)</label>
                    <input type="number" id="choicesPerQuestion" min="2" max="5" value="4" required>
                </div>
            </div>
            <button type="button" id="btnInitializeWorkspace" class="btn btn-primary">Build Configuration
                Matrix</button>
        </div>

        <form id="qcmMasterKeyForm" style="display:none;">
            <h3>Assign Target Answers & Point Weights</h3>
            <p class="text-muted" style="margin-bottom: 20px;">Provide the right choice values and the custom
                coefficient score points allocated for each question row.</p>

            <div id="interactiveMatrixWorkspace"></div>

            <div class="control-actions">
                <button type="submit" class="btn btn-success">Commit Master Key Blueprint</button>
                <button type="button" id="TriggerPrintJob" class="btn btn-secondary">Print Empty Sheet Template</button>
            </div>
        </form>
    </div>

    <div id="printableBubbleDocument">
        <div class="doc-title-block">
            <h1>EXAM ANSWER SHEET - BUBBLE MATRIX READABLE TEMPLATE</h1>
            <p>Instructions: Darken the circles completely using a dark pen. Ensure your identity fields are explicitly
                distinct.</p>
        </div>

        <div class="student-info-grid">
            <div>
                <p><strong>STUDENT ID (CIN/SERIAL):</strong> _______________________</p>
                <p><strong>LAST NAME / NOM:</strong> ____________________________</p>
                <p><strong>FIRST NAME / PRENOM:</strong> _________________________</p>
            </div>
            <div>
                <p><strong>EVALUATION IDENTIFIER:</strong> <span id="lblDocumentExamRef"
                        style="font-family: monospace; font-weight: bold; background: #e9ecef; padding: 2px 6px; border-radius:3px;">#--</span>
                </p>
                <p><strong>CLASS / FILIÈRE:</strong> __________________________</p>
                <p><strong>DATE:</strong> ______________________</p>
            </div>
        </div>

        <div class="sheet-columns-wrapper" id="documentGridInversionTarget"></div>
    </div>

    <script src="/js/qcm-builder.js"></script>
</body>

</html>