/* ============================================================
   qcm-dashboard.js — Dashboard page interactions
   ============================================================ */

// ── Create Modal ──────────────────────────────────────────────

function openModal() {
    document.getElementById('createModal').classList.add('open');
}

function closeModal() {
    document.getElementById('createModal').classList.remove('open');
}

// ── Confirm QCM Modal ─────────────────────────────────────────

function openConfirmModal() {
    document.getElementById('confirmQcmModal').classList.add('open');
}

function closeConfirmModal() {
    document.getElementById('confirmQcmModal').classList.remove('open');
}

// ── Modify Modal ──────────────────────────────────────────────

function openModifyModal(examId, currentFormat) {
    document.getElementById('modify_exam_id').value = examId;
    document.getElementById('modify_format').value = currentFormat;
    document.getElementById('modifyModal').classList.add('open');
}

function closeModifyModal() {
    document.getElementById('modifyModal').classList.remove('open');
}

// ── Student Modify Modal ──────────────────────────────────────

function openStudentModifyModal(btn) {
    const examId = btn.getAttribute('data-exam-id');
    const studentId = btn.getAttribute('data-student-id');
    const cin = btn.getAttribute('data-cin');
    const fullname = btn.getAttribute('data-fullname');
    const email = btn.getAttribute('data-email');
    const note = btn.getAttribute('data-note');
    let statut = btn.getAttribute('data-statut');

    document.getElementById('student_modify_exam_id').value = examId;
    document.getElementById('student_modify_student_id').value = studentId;
    document.getElementById('student_modify_cin').innerText = cin;
    document.getElementById('student_modify_name').innerText = fullname;
    document.getElementById('student_modify_email').innerText = email;
    document.getElementById('student_modify_note').value = note;
    
    // Default to EN_ATTENTE if status is --
    if (statut === '--' || !statut) {
        statut = 'EN_ATTENTE';
    }
    document.getElementById('student_modify_statut').value = statut;

    document.getElementById('studentModifyModal').classList.add('open');
}

function closeStudentModifyModal() {
    document.getElementById('studentModifyModal').classList.remove('open');
}

// ── Students Drawer Toggle ────────────────────────────────────

function toggleStudents(examId) {
    const drawer = document.getElementById('students-drawer-' + examId);
    if (drawer) {
        if (drawer.style.display === 'none') {
            drawer.style.display = 'table-row';
        } else {
            drawer.style.display = 'none';
        }
    }
}

// ── Backdrop click-to-close ───────────────────────────────────

document.getElementById('createModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

document.getElementById('modifyModal').addEventListener('click', function (e) {
    if (e.target === this) closeModifyModal();
});

document.getElementById('confirmQcmModal').addEventListener('click', function (e) {
    if (e.target === this) closeConfirmModal();
});

document.getElementById('studentModifyModal').addEventListener('click', function (e) {
    if (e.target === this) closeStudentModifyModal();
});

// ── Create Exam form (AJAX) ───────────────────────────────────

const createForm = document.querySelector('#createModal form');
if (createForm) {
    createForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = createForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Création...';
        }

        const formData = new FormData(createForm);

        fetch('?page=api-create-exam', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                const format = data.format;
                const examId = data.exam_id;
                const courseId = data.enseignement_id;

                closeModal();

                if (format === 'NON_QCM') {
                    window.location.href = '?page=qcm-dashboard&course_id=' + courseId;
                } else {
                    openConfirmModal();

                    document.getElementById('btnQcmNow').onclick = function () {
                        window.location.href = '?page=qcm-create&exam_id=' + examId;
                    };

                    document.getElementById('btnQcmLater').onclick = function () {
                        window.location.href = '?page=qcm-dashboard&course_id=' + courseId;
                    };
                }
            } else {
                alert('Erreur: ' + (res.message || 'Impossible de créer l\'examen.'));
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Créer';
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Une erreur réseau s\'est produite.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Créer';
            }
        });
    });
}

// ── Modify Exam form (AJAX) ──────────────────────────────────

const modifyForm = document.querySelector('#modifyModal form');
if (modifyForm) {
    modifyForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = modifyForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Enregistrement...';
        }

        const formData = new FormData(modifyForm);

        fetch('?page=api-modify-exam', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                window.location.reload();
            } else {
                alert('Erreur: ' + (res.message || 'Impossible de modifier l\'examen.'));
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Enregistrer';
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Une erreur réseau s\'est produite.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Enregistrer';
            }
        });
    });
}

// ── Student Modify Grade Form (AJAX) ──────────────────────────

const studentModifyForm = document.querySelector('#studentModifyModal form');
if (studentModifyForm) {
    studentModifyForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitBtn = studentModifyForm.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Enregistrement...';
        }

        const formData = new FormData(studentModifyForm);

        fetch('?page=api-modify-student-grade', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                window.location.reload();
            } else {
                alert('Erreur: ' + (res.message || 'Impossible de modifier la note.'));
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Enregistrer';
                }
            }
        })
        .catch(err => {
            console.error(err);
            alert('Une erreur réseau s\'est produite.');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Enregistrer';
            }
        });
    });
}

