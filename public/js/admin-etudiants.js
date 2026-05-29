
// Dépend de : admin-modals.js (chargé avant)
// CLASSES_BY_FILIERE est injecté par la vue PHP via une balise <script> inline

function updateClasses(filiere) {
    const sel     = document.getElementById('sel-classe');
    const classes = (typeof CLASSES_BY_FILIERE !== 'undefined' ? CLASSES_BY_FILIERE[filiere] : null) || [];
    // on vide les options actuelles
    sel.innerHTML = '<option value="">— Choisir une classe —</option>';

    // on ajoute les nouvelles options
    classes.forEach(cl => {
        const opt       = document.createElement('option');
        opt.value       = cl;
        opt.textContent = cl;
        sel.appendChild(opt);
    });
}
