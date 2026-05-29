
function ouvrirModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.add('etu-modal-backdrop--open'); //najouti class li taffichi modal
    document.body.style.overflow = 'hidden'; // nebloki ll scrole men teli
}

function fermerModal(id) {
    const m = document.getElementById(id);
    if (!m) return;
    m.classList.remove('etu-modal-backdrop--open');
    document.body.style.overflow = ''; // reaktiviti ll scrole  
}

function fermerModalBackdrop(event, id) {
    if (event.target === event.currentTarget) fermerModal(id); // ken clickit 3la backdrop (li howa el modal backdrop), yetsaker el modal
}

// listener 3la keydown pour fermer les modals avec la touche Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.etu-modal-backdrop--open').forEach(m => {
            m.classList.remove('etu-modal-backdrop--open');
            document.body.style.overflow = '';
        });
    }
});
