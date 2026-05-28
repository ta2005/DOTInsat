// public/js/qcm-builder.js
document.addEventListener('DOMContentLoaded', () => {
    // ── ROOT-LEVEL PRINT HEADERS SUPPRESSION ENGINE ─────────────────
    const printStyle = document.createElement('style');
    printStyle.innerHTML = `
        /* Declared at the root level to bypass nested media query parsing bugs */
        @media print {
    @page {
        margin: 0mm !important; /* Tells the browser: no room for your headers */
    }
    body {
        margin: 15mm !important; /* Puts the margin back inside the document so your sheet doesn't get cut off by the printer */
    }
}
        @media print {
            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            /* Hide setup panels, administrative tools, and utility buttons */
            body > *, #qcmMasterKeyForm, #interactiveMatrixWorkspace, button, .no-print {
                display: none !important;
            }
            /* Isolate and protect the printable OMR document canvas boundary */
            #printableBubbleDocument {
                display: block !important;
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 210mm !important;   /* Hardcoded standard A4 width */
                height: 297mm !important;  /* Hardcoded standard A4 height */
                padding: 20mm 15mm !important; /* Safe padding so content never clips the edge */
                box-sizing: border-box !important;
                margin: 0 !important;
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    `;
    document.head.appendChild(printStyle);

    // Structural Controls Interaction Anchors
    const btnInitialize = document.getElementById('btnInitializeWorkspace');
    const masterForm = document.getElementById('qcmMasterKeyForm');
    const matrixWorkspace = document.getElementById('interactiveMatrixWorkspace');

    // Printable Vector Layout DOM Anchors
    const printableDocument = document.getElementById('printableBubbleDocument');
    const documentGridTarget = document.getElementById('documentGridInversionTarget');
    const lblDocumentExamRef = document.getElementById('lblDocumentExamRef');
    const triggerPrintBtn = document.getElementById('TriggerPrintJob');

    const choiceDictionary = ['A', 'B', 'C', 'D', 'E'];

    btnInitialize.addEventListener('click', () => {
        const examIdStr = document.getElementById('controleId').value;
        const totalQ = parseInt(document.getElementById('totalQuestions').value);
        const totalC = parseInt(document.getElementById('choicesPerQuestion').value);

        if (!examIdStr || isNaN(totalQ) || isNaN(totalC)) {
            alert('Please specify valid initialization variables before proceeding.');
            return;
        }
        if (totalQ < 10 || totalQ > 40) {
            alert('Question boundary verification constraint failed. Please pick a number between 10 and 40.');
            return;
        }
        if (totalC < 2 || totalC > 5) {
            alert('Choices dimension constraint failed. Must be between 2 and 5 items.');
            return;
        }

        const examId = parseInt(examIdStr);

        // 1. Compile the Interactive Professor Workspace UI Matrix 
        matrixWorkspace.innerHTML = '';
        for (let q = 1; q <= totalQ; q++) {
            const card = document.createElement('div');
            card.className = 'matrix-item';

            let choicesUiPayload = '';
            for (let c = 0; c < totalC; c++) {
                const characterKey = choiceDictionary[c];
                choicesUiPayload += `
                    <label class="bubble-label">
                        <input type="radio" name="matrix_correct[q${q}]" value="${characterKey}" ${c === 0 ? 'checked' : ''}>
                        ${characterKey}
                    </label>
                `;
            }

            card.innerHTML = `
                <div class="matrix-header">
                    <strong>Question ${q}</strong>
                    <div>
                        <input type="number" name="matrix_weight[q${q}]" value="1" min="0.25" step="0.25" style="width:55px; text-align:center; padding:2px;"> <span style="font-size:0.8rem; font-weight:600;">pts</span>
                    </div>
                </div>
                <div class="choice-row">${choicesUiPayload}</div>
            `;
            matrixWorkspace.appendChild(card);
        }

        // 2. Compile the Empty Vector Printable Document Layout Grid View
        lblDocumentExamRef.innerText = `CTRL_REF_ID_${examId}`;
        documentGridTarget.innerHTML = '';

        for (let q = 1; q <= totalQ; q++) {
            const rowLine = document.createElement('div');
            rowLine.className = 'sheet-row-line';

            let dynamicBubblesVector = '';
            for (let c = 0; c < totalC; c++) {
                dynamicBubblesVector += `<div class="vector-bubble-circle">${choiceDictionary[c]}</div>`;
            }

            rowLine.innerHTML = `
                <div class="sheet-question-num">${q}.</div>
                <div class="sheet-bubbles-container">${dynamicBubblesVector}</div>
            `;
            documentGridTarget.appendChild(rowLine);
        }

        masterForm.style.display = 'block';
        printableDocument.style.display = 'block';
    });

    triggerPrintBtn.addEventListener('click', () => {
        window.print();
    });

    masterForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const examId = document.getElementById('controleId').value;
        const totalQ = parseInt(document.getElementById('totalQuestions').value);
        const totalC = parseInt(document.getElementById('choicesPerQuestion').value);

        const keyMapPayload = {
            exam_id: parseInt(examId),
            total_questions: totalQ,
            choices_per_question: totalC,
            grading_matrix: {}
        };

        for (let q = 1; q <= totalQ; q++) {
            const correctChecked = masterForm.querySelector(`input[name="matrix_correct[q${q}]"]:checked`);
            const pointWeightValue = masterForm.querySelector(`input[name="matrix_weight[q${q}]"]`);

            keyMapPayload.grading_matrix[`q${q}`] = {
                correct_choice: correctChecked.value,
                weight: parseFloat(pointWeightValue.value)
            };
        }

        try {
            const response = await fetch('/?page=api-save-template', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(keyMapPayload)
            });

            const responseParsed = await response.json();
            if (responseParsed.success) {
                alert('Master answer configuration blueprint registered successfully into flat storage files!');
            } else {
                alert('Storage validation compilation failure exception: ' + responseParsed.message);
            }
        } catch (fault) {
            console.error('Network exception captured: ', fault);
            alert('Communication stream aborted. Verify infrastructure logs.');
        }
    });
});