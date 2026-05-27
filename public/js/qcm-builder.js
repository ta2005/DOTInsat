// public/js/qcm-builder.js
document.addEventListener('DOMContentLoaded', () => {

    const btnInitialize    = document.getElementById('btnInitializeWorkspace');
    const masterForm       = document.getElementById('qcmMasterKeyForm');
    const matrixWorkspace  = document.getElementById('interactiveMatrixWorkspace');
    const printableDoc     = document.getElementById('printableBubbleDocument');
    const documentGrid     = document.getElementById('documentGridInversionTarget');
    const lblExamRef       = document.getElementById('lblDocumentExamRef');
    const triggerPrintBtn  = document.getElementById('TriggerPrintJob');

    const CHOICES = ['A', 'B', 'C', 'D', 'E'];

    // ── Générer la matrice ──────────────────────────────────────────────────
    btnInitialize.addEventListener('click', () => {
        const examIdStr = document.getElementById('controleId').value;
        const totalQ    = parseInt(document.getElementById('totalQuestions').value);
        const totalC    = parseInt(document.getElementById('choicesPerQuestion').value);

        if (!examIdStr || isNaN(totalQ) || isNaN(totalC)) {
            alert('Veuillez remplir tous les champs avant de générer la matrice.');
            return;
        }
        if (totalQ < 10 || totalQ > 40) {
            alert('Le nombre de questions doit être entre 10 et 40.');
            return;
        }
        if (totalC < 2 || totalC > 5) {
            alert('Le nombre de choix doit être entre 2 et 5.');
            return;
        }

        const examId = parseInt(examIdStr);

        // Matrice interactive
        matrixWorkspace.innerHTML = '';
        for (let q = 1; q <= totalQ; q++) {
            let choicesHtml = '';
            for (let c = 0; c < totalC; c++) {
                const ch = CHOICES[c];
                choicesHtml += `
                    <label class="bubble-label">
                        <input type="radio" name="matrix_correct[q${q}]" value="${ch}" ${c === 0 ? 'checked' : ''}>
                        ${ch}
                    </label>`;
            }

            const card = document.createElement('div');
            card.className = 'matrix-item';
            card.innerHTML = `
                <div class="matrix-header">
                    <strong>Question ${q}</strong>
                    <div>
                        <input type="number" name="matrix_weight[q${q}]"
                               value="1" min="0.25" step="0.25">
                        <span style="font-size:.8rem; font-weight:600;">pts</span>
                    </div>
                </div>
                <div class="choice-row">${choicesHtml}</div>`;
            matrixWorkspace.appendChild(card);
        }

        // Feuille imprimable
        lblExamRef.innerText = `#${examId}`;
        documentGrid.innerHTML = '';
        for (let q = 1; q <= totalQ; q++) {
            let bubblesHtml = '';
            for (let c = 0; c < totalC; c++) {
                bubblesHtml += `<div class="vector-bubble-circle">${CHOICES[c]}</div>`;
            }
            const row = document.createElement('div');
            row.className = 'sheet-row-line';
            row.innerHTML = `
                <div class="sheet-question-num">${q}.</div>
                <div class="sheet-bubbles-container">${bubblesHtml}</div>`;
            documentGrid.appendChild(row);
        }

        masterForm.style.display  = 'block';
        printableDoc.style.display = 'block';
    });

    // ── Impression ──────────────────────────────────────────────────────────
    triggerPrintBtn.addEventListener('click', () => window.print());

    // ── Soumission vers l'API ────────────────────────────────────────────────
    masterForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const examId = parseInt(document.getElementById('controleId').value);
        const totalQ = parseInt(document.getElementById('totalQuestions').value);
        const totalC = parseInt(document.getElementById('choicesPerQuestion').value);

        const payload = {
            exam_id:              examId,
            total_questions:      totalQ,
            choices_per_question: totalC,
            grading_matrix:       {}
        };

        for (let q = 1; q <= totalQ; q++) {
            const correct = masterForm.querySelector(`input[name="matrix_correct[q${q}]"]:checked`);
            const weight  = masterForm.querySelector(`input[name="matrix_weight[q${q}]"]`);
            payload.grading_matrix[`q${q}`] = {
                correct_choice: correct.value,
                weight:         parseFloat(weight.value)
            };
        }

        try {
            // ↓ Route corrigée pour correspondre à ton router (?page=api-save-template)
            const res  = await fetch('/?page=api-save-template', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify(payload)
            });

            const data = await res.json();

            if (data.success) {
                alert(`Clé maître enregistrée pour l'examen #${examId} !`);
            } else {
                alert('Erreur : ' + data.message);
            }
        } catch (err) {
            console.error('Erreur réseau :', err);
            alert('Impossible de joindre le serveur. Vérifiez votre connexion.');
        }
    });
});