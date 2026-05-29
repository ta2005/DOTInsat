

// ouvrir les panels refus
function ouvrirApprouver(id) {
    fermerPanels(id);
    const p = document.getElementById('panel-approuver-' + id);
    if (p) { p.style.display = 'block'; p.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
}
// ouvrir les panels refus
function ouvrirRefuser(id) {
    fermerPanels(id);
    const p = document.getElementById('panel-refuser-' + id);
    if (p) { p.style.display = 'block'; p.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
}
// fermer les panels refus
function fermerPanels(id) {
    const a = document.getElementById('panel-approuver-' + id);
    const r = document.getElementById('panel-refuser-'   + id);
    if (a) a.style.display = 'none';
    if (r) r.style.display = 'none';
}
