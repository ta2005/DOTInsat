// dictionnaire labels pour l'affichage dans l'interface
const labels = { DS: 'DS', EXAM: 'Examen', TP: 'TP' };

// alias pour compatibilité
const profsData = profsJS;

// matière actuellement sélectionnée (string, valeur du select)
let currentMatiere = null;

/**
 * CORRECTION : on normalise la clé en string ET en int pour couvrir les deux cas.
 * On cherche d'abord en string (comportement JS par défaut des object keys),
 * puis en int si pas trouvé.
 */
function getFromData(data, key) {
    // les clés d'un objet JS sont toujours des strings
    const strKey = String(key);
    if (data[strKey] !== undefined) return data[strKey];
    // fallback int (cas où l'objet a été créé avec des clés numériques)
    const intKey = parseInt(key, 10);
    if (!isNaN(intKey) && data[intKey] !== undefined) return data[intKey];
    return undefined;
}

// appelé quand l'étudiant choisit une matière
function onMatiereChange(sel) {
    currentMatiere = sel.value;

    const types   = getFromData(notesData, currentMatiere) ?? {};
    const typeKeys = Object.keys(types);

    const evalSelect = document.getElementById('rec-eval');
    evalSelect.innerHTML = '<option value="" disabled selected>Choisir</option>';

    // CORRECTION : si aucun type trouvé, on cache le bloc et on arrête
    if (typeKeys.length === 0) {
        document.getElementById('eval-block').style.display      = 'none';
        document.getElementById('note-info-block').style.display = 'none';
        document.getElementById('controle-id-input').value       = '';
        console.warn('[reclamation] Aucun type d\'évaluation pour matiere_id:', currentMatiere, notesData);
        return;
    }

    // remplir le select des types d'évaluation
    typeKeys.forEach(type => {
        const opt = document.createElement('option');
        opt.value       = type;
        opt.textContent = labels[type] ?? type;
        evalSelect.appendChild(opt);
    });

    document.getElementById('eval-block').style.display = 'block';

    // afficher le prof dès la sélection de la matière
    const prof = getFromData(profsData, currentMatiere);
    document.getElementById('prof-val').textContent  = prof ?? '—';
    document.getElementById('note-val').textContent  = '—';
    document.getElementById('note-info-block').style.display = 'block';

    // reset controle_id tant qu'aucun type n'est choisi
    document.getElementById('controle-id-input').value = '';

    // CORRECTION : si une seule option disponible, la sélectionner automatiquement
    if (typeKeys.length === 1) {
        evalSelect.value = typeKeys[0];
        onEvalChange();
    }
}

// appelé quand l'étudiant choisit un type d'évaluation (DS / EXAM / TP)
function onEvalChange() {
    const evalVal = document.getElementById('rec-eval').value;
    if (!currentMatiere || !evalVal) return;

    const types = getFromData(notesData, currentMatiere) ?? {};
    const entry = types[evalVal];
    const prof  = getFromData(profsData, currentMatiere);

    document.getElementById('prof-val').textContent = prof ?? '—';

    // CORRECTION : vérifier que entry existe avant d'accéder à ses propriétés
    if (entry && entry.note !== undefined && entry.note !== null) {
        document.getElementById('note-val').textContent = entry.note + ' / 20';
    } else {
        document.getElementById('note-val').textContent = '—';
    }

    // stocker le controle_id dans le champ caché pour store()
    document.getElementById('controle-id-input').value = entry?.controle_id ?? '';

    document.getElementById('note-info-block').style.display = 'block';
}
