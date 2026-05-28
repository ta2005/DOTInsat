// public/js/admin-enseignants.js
// Dépend de : admin-modals.js (chargé avant)

(function () {
    const input = document.getElementById('ens-search-input');
    const rows  = document.querySelectorAll('.ens-row');
    const count = document.getElementById('ens-count');
    if (!input) return;

    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const q = this.value.toLowerCase().trim();
            let n = 0;
            rows.forEach(row => {
                const match = !q || (row.dataset.search || '').includes(q);
                row.style.display = match ? '' : 'none';
                if (match) n++;
            });
            count.textContent = n + ' enseignant' + (n !== 1 ? 's' : '')
                + (q ? ' · « ' + this.value + ' »' : '');
        }, 180);
    });
})();
