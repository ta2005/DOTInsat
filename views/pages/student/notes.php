<?php


require_once BASE_PATH . '/app/Repositories/NotesRepository.php';



$etudiantId = $_SESSION['user_id'] ?? null;

if (!$etudiantId) {
    header('Location: /login');
    exit;
}

$filiereRaw = strtoupper(trim($_SESSION['filiere'] ?? ''));
preg_match('/^([A-Z]+)/', $filiereRaw, $mF);
$filiere = $mF[1] ?? 'GL';
preg_match('/^[A-Z]+(\d)/', $filiereRaw, $mN);
$niveau = isset($mN[1]) ? (int)$mN[1] : 2;


// taala hne bch yjib les notes mta3 l'etudiant 7asb ll id mta3ou w filiere w niveau, w bch yjibha mratbin 7asb les semestres (semestre 1 w semestre 2)
$notesRepo = new NotesRepository($pdo);

$semestres = [
    1 => $notesRepo->getNotesBySemestre($etudiantId, $filiere, $niveau, 1),
    2 => $notesRepo->getNotesBySemestre($etudiantId, $filiere, $niveau, 2),
];

// ll hessba

function calculerMoyenneMatiere(array $notes): ?float
{
    $ds = $notes['DS']['note']   ?? null;
    $tp = $notes['TP']['note']   ?? null;
    $ex = $notes['EXAM']['note'] ?? null;

    if ($ds !== null && $tp !== null && $ex !== null)
        return $ds * 0.2 + $tp * 0.2 + $ex * 0.6;

    if ($ds === null && $tp !== null && $ex !== null)
        return $tp * 0.3 + $ex * 0.7;

    if ($ds !== null && $tp === null && $ex !== null)
        return $ds * 0.3 + $ex * 0.7;

    if ($ds !== null && $tp !== null && $ex === null)
        return $ds * 0.4 + $tp * 0.6;

    if ($ex !== null) return $ex;
    if ($ds !== null) return $ds;
    if ($tp !== null) return $tp;

    return null;
}

// moyenne

function calculerMoyenneSemestre(array $matieres): ?float
{
    $total      = 0;
    $totalCoeff = 0;

    foreach ($matieres as $m) {
        $moyenne = calculerMoyenneMatiere($m['notes']);
        if ($moyenne === null) continue;
        $total      += $moyenne * $m['coefficient'];
        $totalCoeff += $m['coefficient'];
    }

    return $totalCoeff === 0 ? null : $total / $totalCoeff;
}

$moyenneS1 = calculerMoyenneSemestre($semestres[1]);
$moyenneS2 = calculerMoyenneSemestre($semestres[2]);

if ($moyenneS1 !== null && $moyenneS2 !== null) {
    $moyenneAnnuelle = ($moyenneS1 + $moyenneS2) / 2;
} elseif ($moyenneS1 !== null) {
    $moyenneAnnuelle = $moyenneS1;
} elseif ($moyenneS2 !== null) {
    $moyenneAnnuelle = $moyenneS2;
} else {
    $moyenneAnnuelle = null;
}



$fmt = fn(?float $n) => $n !== null ? number_format($n, 2) : '--.--';

$labelStatut = [
    'EN_ATTENTE' => 'En attente',
    'CORRIGE'    => 'Corrigé',
    'VERIFIE'    => 'Vérifié',
    'CONTESTE'   => 'Contesté',
];
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Notes — INSAT</title>


    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/notifications.css">

    <!-- CSS PAGE -->
    <link rel="stylesheet" href="/css/calculator.css">
    <link rel="stylesheet" href="/css/notes.css">

</head>

<body>

// header global

<?php require BASE_PATH . '/views/layouts/header.php'; ?>


<div class="calculator-page">

    <div class="container">

        <div class="calculator-wrapper">

         

            <div class="calculator-header">

                <h1 class="calculator-title">
                    Mes Notes
                </h1>

                <p class="calculator-subtitle">
                    Relevé de notes officiel
                </p>

                <div class="student-level">

                    <span class="student-level-label">
                        Filière :
                    </span>

                    <span class="student-level-value">
                        <?php echo htmlspecialchars($filiere . $niveau); ?>
                    </span>

                </div>

            </div>

                < --semestres grid, fih les 2 semestres w fiha les matieres w les notes mta3hom -- >
            <div class="semesters-grid">

              

                    <h2>Semestre 1</h2>

                    <?php if (empty($semestres[1])): ?>

                        <p style="color:#9a9aa0;">Aucune matière pour ce semestre.</p>

                    <?php else: ?>

                        <?php foreach ($semestres[1] as $matiere):

                            $notes   = $matiere['notes'];
                            $moyenne = calculerMoyenneMatiere($notes);

                        ?>

                        <div class="subject-row">

                            <div class="subject-name">
                                <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                <span class="coeff">
                                    (x<?php echo number_format($matiere['coefficient'], 2); ?>)
                                </span>
                            </div>

                            <div class="input-group">

                                <?php if (isset($notes['DS'])): ?>
                                    <span
                                        class="note-display"
                                        title="<?php echo $labelStatut[$notes['DS']['statut']] ?? $notes['DS']['statut']; ?>"
                                    >
                                        <span class="note-display__label">DS</span>
                                        <span class="note-display__value"><?php echo number_format($notes['DS']['note'], 2); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if (isset($notes['TP'])): ?>
                                    <span
                                        class="note-display"
                                        title="<?php echo $labelStatut[$notes['TP']['statut']] ?? $notes['TP']['statut']; ?>"
                                    >
                                        <span class="note-display__label">TP</span>
                                        <span class="note-display__value"><?php echo number_format($notes['TP']['note'], 2); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if (isset($notes['EXAM'])): ?>
                                    <span
                                        class="note-display"
                                        title="<?php echo $labelStatut[$notes['EXAM']['statut']] ?? $notes['EXAM']['statut']; ?>"
                                    >
                                        <span class="note-display__label">Exam</span>
                                        <span class="note-display__value"><?php echo number_format($notes['EXAM']['note'], 2); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if ($moyenne !== null): ?>
                                    <span class="note-display note-display--moyenne">
                                        <span class="note-display__label">Moy</span>
                                        <span class="note-display__value"><?php echo number_format($moyenne, 2); ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="note-display note-display--vide">
                                        <span class="note-display__value">—</span>
                                    </span>
                                <?php endif; ?>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

                <!-- semestre 2 -->
            

                <div class="semester-card">

                    <h2>Semestre 2</h2>

                    <?php if (empty($semestres[2])): ?>

                        <p style="color:#9a9aa0;">Aucune matière pour ce semestre.</p>

                    <?php else: ?>

                        <?php foreach ($semestres[2] as $matiere):

                            $notes   = $matiere['notes'];
                            $moyenne = calculerMoyenneMatiere($notes);

                        ?>

                        <div class="subject-row">

                            <div class="subject-name">
                                <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                <span class="coeff">
                                    (x<?php echo number_format($matiere['coefficient'], 2); ?>)
                                </span>
                            </div>

                            <div class="input-group">

                                <?php if (isset($notes['DS'])): ?>
                                    <span
                                        class="note-display"
                                        title="<?php echo $labelStatut[$notes['DS']['statut']] ?? $notes['DS']['statut']; ?>"
                                    >
                                        <span class="note-display__label">DS</span>
                                        <span class="note-display__value"><?php echo number_format($notes['DS']['note'], 2); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if (isset($notes['TP'])): ?>
                                    <span
                                        class="note-display"
                                        title="<?php echo $labelStatut[$notes['TP']['statut']] ?? $notes['TP']['statut']; ?>"
                                    >
                                        <span class="note-display__label">TP</span>
                                        <span class="note-display__value"><?php echo number_format($notes['TP']['note'], 2); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if (isset($notes['EXAM'])): ?>
                                    <span
                                        class="note-display"
                                        title="<?php echo $labelStatut[$notes['EXAM']['statut']] ?? $notes['EXAM']['statut']; ?>"
                                    >
                                        <span class="note-display__label">Exam</span>
                                        <span class="note-display__value"><?php echo number_format($notes['EXAM']['note'], 2); ?></span>
                                    </span>
                                <?php endif; ?>

                                <?php if ($moyenne !== null): ?>
                                    <span class="note-display note-display--moyenne">
                                        <span class="note-display__label">Moy</span>
                                        <span class="note-display__value"><?php echo number_format($moyenne, 2); ?></span>
                                    </span>
                                <?php else: ?>
                                    <span class="note-display note-display--vide">
                                        <span class="note-display__value">—</span>
                                    </span>
                                <?php endif; ?>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </div>

         
            <!-- Resultt -->
          *

            <div class="results-card">

                <h2>Résultats</h2>

                <div class="results-grid">

                    <div class="result-box">
                        <h3>Semestre 1</h3>
                        <span><?php echo $fmt($moyenneS1); ?></span>
                    </div>

                    <div class="result-box">
                        <h3>Semestre 2</h3>
                        <span><?php echo $fmt($moyenneS2); ?></span>
                    </div>

                    <div class="result-box total">
                        <h3>Moyenne Annuelle</h3>
                        <span><?php echo $fmt($moyenneAnnuelle); ?></span>
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
