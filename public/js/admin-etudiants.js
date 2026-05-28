// public/js/admin-etudiants.js
// Dépend de : admin-modals.js (chargé avant)
// CLASSES_BY_FILIERE est injecté par la vue PHP via une balise <script> inline

function updateClasses(filiere) {
    const sel     = document.getElementById('sel-classe');
    const classes = (typeof CLASSES_BY_FILIERE !== 'undefined' ? CLASSES_BY_FILIERE[filiere] : null) || [];

    sel.innerHTML = '<option value="">— Choisir une classe —</option>';

    classes.forEach(cl => {
        const opt       = document.createElement('option');
        opt.value       = cl;
        opt.textContent = cl;
        sel.appendChild(opt);
    });
}
