<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Professor Panel - Automated QCM Scanner Engine</title>
    <link rel="stylesheet" href="/css/main.css">
    <style>
        .scanner-layout {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 30px;
            margin-top: 20px;
        }

        /* Drag and Drop Capture Frame Area */
        .dropzone-container {
            border: 3px dashed #0d6efd;
            background: #f8f9fa;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .dropzone-container.dragover {
            background: #e7f1ff;
            border-color: #0a58ca;
        }

        /* Hidden Processing Canvas Layout */
        #processingCanvas {
            display: none;
            max-width: 100%;
            border: 1px solid #dee2e6;
            margin-top: 20px;
        }

        /* Live Results Processing Feed Sidebar Layout */
        .results-sidebar {
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
            height: max-content;
        }

        .log-stream {
            max-height: 300px;
            overflow-y: auto;
            background: #212529;
            color: #0dfd53;
            font-family: monospace;
            padding: 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 15px;
        }

        .log-entry {
            margin-bottom: 6px;
            border-bottom: 1px solid #343a40;
            padding-bottom: 4px;
        }

        .score-badge {
            display: inline-block;
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
            margin: 15px 0;
        }

        .status-pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: bold;
            background: #ffc107;
            color: #000;
        }

        .status-pill.success {
            background: #198754;
            color: #fff;
        }
    </style>
</head>

<body>

    <div class="container" style="padding: 30px; max-width: 1300px; margin: 0 auto;">
        <h2>Automated Sheet Processing Stream Engine</h2>
        <p class="text-muted">Upload or drop high-contrast student bubble sheet images here to calculate grades in
            real-time and automatically sync them with your database.</p>

        <div class="scanner-layout">

            <div>
                <div id="dropzone" class="dropzone-container">
                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="#0d6efd" viewBox="0 0 16 16"
                        style="margin-bottom:15px;">
                        <path
                            d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                        <path
                            d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z" />
                    </svg>
                    <h4>Drag & Drop Student Sheet Image Here</h4>
                    <p class="text-muted">Supports PNG, JPG, or JPEG source files</p>
                    <input type="file" id="fileFallbackInput" accept="image/*" style="display: none;">
                    <button type="button" class="btn btn-primary"
                        onclick="document.getElementById('fileFallbackInput').click()">Browse Files</button>
                </div>

                <canvas id="processingCanvas"></canvas>
            </div>

            <div class="results-sidebar">
                <h3>Processing Console</h3>
                <hr>
                <div style="margin-bottom: 15px;">
                    <label><strong>Current Student Session ID:</strong></label>
                    <div id="lblSessionStudent" style="font-size: 1.2rem; font-weight: bold; color: #495057;">Pending
                        Scan...</div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label><strong>Execution Status:</strong></label>
                    <div><span id="pillStatus" class="status-pill">Awaiting Target Payload</span></div>
                </div>

                <div>
                    <label><strong>Calculated Return Grade Score:</strong></label>
                    <div><span id="lblCalculatedScore" class="score-badge">0.00</span></div>
                </div>

                <strong>System Engine Activity Log:</strong>
                <div id="logStream" class="log-stream">
                    <div class="log-entry">[System] Core engine active. Awaiting image upload...</div>
                </div>
            </div>

        </div>
    </div>

    <script src="/js/qcm-scanner.js"></script>
</body>

</html>