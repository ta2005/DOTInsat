// public/js/admin-modals.js
// Fonctions modals partagées entre etudiants.php et enseignants.php

function ouvrirModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add('etu-modal-backdrop--open');
    document.body.style.overflow = 'hidden';
}

function fermerModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('etu-modal-backdrop--open');
    document.body.style.overflow = '';
}

function fermerModalBackdrop(event, id) {
    if (event.target === event.currentTarget) fermerModal(id);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.etu-modal-backdrop--open').forEach(m => {
            m.classList.remove('etu-modal-backdrop--open');
            document.body.style.overflow = '';
        });
    }
});
