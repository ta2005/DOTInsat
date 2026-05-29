
// Dépend de : admin-modals.js deja chargitou fi page enseignants.php

(function () {
    const input = document.getElementById('ens-search-input'); // input de recherche
    const rows  = document.querySelectorAll('.ens-row'); // les lignes du tableau, chaque ligne a une data-search qui contient les champs à rechercher (nom, prenom, email)
    const count = document.getElementById('ens-count'); // l'élément qui affiche le nombre de résultats et la requete de recherche
    if (!input) return;

    let timer;
    // Listener 3la input pour faire la recherche
    input.addEventListener('input', function () {
        clearTimeout(timer);// bach ne3adiwsh recherche 3la kol harf yektbouh, ken yebda yektb fi input, nstannaw 180ms, ken ma ktabch 7aja jdida, n3adiw l recherche
        timer = setTimeout(() => {
            const q = this.value.toLowerCase().trim();
            let n = 0;
            rows.forEach(row => {
                const match = !q || (row.dataset.search || '').includes(q);// ken ma fiha hata 7aja (q vide) aw ken data-search fih q n'affichiha sinon n'khabiha
                row.style.display = match ? '' : 'none';
                if (match) n++;
            });
            count.textContent = n + ' enseignant' + (n !== 1 ? 's' : '');
        }, 180);
    });
})();
