// public/js/prof-reclamations.js

function ouvrirAccepter(id) {
    fermerPanels(id);
    const panel = document.getElementById('panel-accepter-' + id);
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function ouvrirRefuser(id) {
    fermerPanels(id);
    const panel = document.getElementById('panel-refuser-' + id);
    panel.style.display = 'block';
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function fermerPanels(id) {
    const a = document.getElementById('panel-accepter-' + id);
    const r = document.getElementById('panel-refuser-'  + id);
    if (a) a.style.display = 'none';
    if (r) r.style.display = 'none';
}
