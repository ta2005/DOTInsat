// ta5e4 notet makanch trajja3 null ken input vide
// ken wa7ed yet5oubeth yekteb 7aja negative ywalli 0
// ken wa7ed yet5oubeth yekteb 7aja akthr men 20 ywalli 20
const recupererNote = (input) => {
    if (!input || input.value === "") return null;
    let val = parseFloat(input.value);
    if (val < 0) return 0;
    if (val > 20) return 20;
    return val;
};

// On isole le calcul par semestre pour éviter de répéter le code
const calculerMoyenneSemestre = (groups) => {
    let totalScore = 0;
    let totalCoeff = 0;

    groups.forEach(group => {
        // na9rou lvaleur mta3 lattribut li défininah data-coeff fl php, elli hia lenna lcoefficient
        let coeff = parseFloat(group.getAttribute('data-coeff'));
        
        // On cherche si les cases HTML existent réellement dans ce groupe
        let inputDS = group.querySelector('input[placeholder="DS"]');
        let inputEx = group.querySelector('input[placeholder="Ex"]');
        let inputTP = group.querySelector('input[placeholder="TP"]');

        // SÉCURITÉ CRITIQUE : On ne récupère la note QUE si la case existe dans le HTML.
        // Si le PHP n'a pas créé de case TP pour cette matière, inputTP est "null", 
        // donc la variable "tp" reste "null" d'office
        let ds = inputDS ? recupererNote(inputDS) : null;
        let ex = inputEx ? recupererNote(inputEx) : null;
        let tp = inputTP ? recupererNote(inputTP) : null;

        let moyMatiere = 0;

        // el calcul
        if (ds !== null && tp !== null && ex !== null) {
            moyMatiere = (ds * 0.2) + (tp * 0.2) + (ex * 0.6);
        } 
        else if (ds === null && tp !== null && ex !== null) {
            moyMatiere = (tp * 0.3) + (ex * 0.7);
        } 
        else if (ds !== null && tp === null && ex !== null) {
            moyMatiere = (ds * 0.3) + (ex * 0.7);
        } 
        else if (ex !== null) {
            moyMatiere = ex;
        } 
        else {
            return;
        }

        totalScore += moyMatiere * coeff;
        totalCoeff += coeff;
    });

    // ken totalCoeff > 0 na3mlou l3amalia, makanch yekteb 0 w yet3adda
    return totalCoeff > 0 ? (totalScore / totalCoeff) : 0;
};

const calculerMoyenne = () => {
    // queryselector 3al les blocs mta3 les semestres (les fieldsets)
    const blocsSemestres = document.querySelectorAll('fieldset.subjects');
    
    let avgS1 = 0;
    let avgS2 = 0;

    // Calcul du Semestre 1 (Premier bloc subjects)
    if (blocsSemestres[0]) {
        const groupsS1 = blocsSemestres[0].querySelectorAll('.input-group');
        avgS1 = calculerMoyenneSemestre(groupsS1);
        
        // affichage lmoyenne yetbaddel pour le S1
        let affichageS1 = document.querySelector("#average1");
        if (affichageS1) affichageS1.innerText = avgS1.toFixed(2);
    }

    // Calcul du Semestre 2 (Deuxième bloc subjects)
    if (blocsSemestres[1]) {
        const groupsS2 = blocsSemestres[1].querySelectorAll('.input-group');
        avgS2 = calculerMoyenneSemestre(groupsS2);
        
        // affichage lmoyenne yetbaddel pour le S2
        let affichageS2 = document.querySelector("#average2");
        if (affichageS2) affichageS2.innerText = avgS2.toFixed(2);
    }

    // Calcul et affichage de la moyenne générale annuelle
    let moyenneGenerale = (avgS1 + avgS2) / 2;
    let affichageMoyenne = document.querySelector("#year-average");
    if (affichageMoyenne) {
        affichageMoyenne.innerText = moyenneGenerale.toFixed(2);
    }
};

// lEvent Listener li bch ye7seb w yaffichi "en temps reel"
document.querySelectorAll(".matiere").forEach(input => {
    input.addEventListener('input', (e) => {
        // tekteb 7aja akther men 20 to4horlek 20, tekteb 7aja negative to4horlek 0 sob7an allah
        let value = parseFloat(e.target.value);
        if (value > 20) e.target.value = 20;
        if (value < 0) e.target.value = 0;
        calculerMoyenne();
    });
});