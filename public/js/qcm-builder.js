// public/js/qcm-builder.js
document.addEventListener('DOMContentLoaded', () => {
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

        // Explicit boundary input assertions
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

        // Reveal the active configuration areas
        masterForm.style.display = 'block';
        printableDocument.style.display = 'block';
    });

    // Handle Printing System Routine Hooks
    triggerPrintBtn.addEventListener('click', () => {
        window.print();
    });

    // Intercept Data Submission for Server Storage Write Operations
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

        // Scrape array configurations out of dynamic rows
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