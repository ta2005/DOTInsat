// bch nhot cookies bach nsaviw bihom les notes
const setCookie = (name, value, days = 30) => {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${encodeURIComponent(value)};expires=${expires.toUTCString()};path=/`;
};

// nsaviw les notes
const sauvegarderNotes = () => {
    const notes = {};
    document.querySelectorAll('.matiere').forEach(input => {
        if (input.name && input.value !== '') {
            notes[input.name] = input.value;
        }
    });
    setCookie('insat_notes', JSON.stringify(notes), 30);
};

// nrecuperiw les notes mll cookies
const recupererNote = (input) => {
    if (!input) return null;
    const v = input.value.trim();
    if (v === '') return null;
    const n = parseFloat(v);
    if (isNaN(n)) return null;
    return n;
};
//formule coefficients des matieres
const calculerMatiere = (ds, tp, ex) => {
    if (ds !== null && tp !== null && ex !== null) return ds * 0.2 + tp * 0.2 + ex * 0.6;
    if (ds === null && tp !== null && ex !== null) return tp * 0.3 + ex * 0.7;
    if (ds !== null && tp === null && ex !== null) return ds * 0.3 + ex * 0.7;
    if (ds !== null && tp !== null && ex === null) return ds * 0.4 + tp * 0.6;
    if (ex !== null) return ex;
    if (ds !== null) return ds;
    if (tp !== null) return tp;
    return null;
};

const calculerMoyenneSemestre = (card) => {
    let total = 0, coeff = 0;
    card.querySelectorAll('.input-group').forEach(g => {
        const c  = parseFloat(g.dataset.coeff || 0);
        const ds = recupererNote(g.querySelector('input[name$="DS"]'));
        const tp = recupererNote(g.querySelector('input[name$="TP"]'));
        const ex = recupererNote(g.querySelector('input[name$="EX"]'));
        const m  = calculerMatiere(ds, tp, ex);
        if (m === null) return;
        total += m * c;
        coeff += c;
    });
    return coeff === 0 ? null : total / coeff;
};

const calculerMoyenne = () => {
    const sems = document.querySelectorAll('.semester-card');
    let s1 = null, s2 = null;

    if (sems[0]) {
        s1 = calculerMoyenneSemestre(sems[0]);
        document.getElementById('average1').innerText =
            s1 !== null ? s1.toFixed(2) : '--.--';
    }

    if (sems[1]) {
        s2 = calculerMoyenneSemestre(sems[1]);
        document.getElementById('average2').innerText =
            s2 !== null ? s2.toFixed(2) : '--.--';
    }

    let ann = null;
    if (s1 !== null && s2 !== null) ann = (s1 + s2) / 2;
    else if (s1 !== null) ann = s1;
    else if (s2 !== null) ann = s2;

    document.getElementById('year-average').innerText =
        ann !== null ? ann.toFixed(2) : '--.--';
};

document.querySelectorAll('.matiere').forEach(input => {
    input.addEventListener('input', (e) => {
        const v = parseFloat(e.target.value);
        if (!isNaN(v)) {
            if (v < 0)  e.target.value = 0;
            if (v > 20) e.target.value = 20;
        }
        sauvegarderNotes();
        calculerMoyenne();
    });
});
