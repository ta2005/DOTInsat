<?php
// views/pages/student/examens/calculator.php

require_once BASE_PATH . '/app/Repositories/MatiereRepository.php';

// $filiere et $niveau sont injectés par HomeController::calculMoyenne()
// Ne pas les recalculer ici pour éviter d'utiliser $_SESSION['annee'] (= année scolaire ex: 2024)
if (!isset($filiere) || !isset($niveau)) {
    $filiereRaw = strtoupper(trim($_SESSION['filiere'] ?? ''));
    preg_match('/^([A-Z]+)/', $filiereRaw, $mF);
    $filiere = $mF[1] ?? 'GL';
    preg_match('/^[A-Z]+(\d)/', $filiereRaw, $mN);
    $niveau = isset($mN[1]) ? (int)$mN[1] : 2;
}

// Année scolaire pour affichage uniquement
$anneeAffichage = $_SESSION['annee'] ?? '';

$matiereRepo = new MatiereRepository($pdo);
$matieresS1  = $matiereRepo->getMatieres($filiere, $niveau, 1);
$matieresS2  = $matiereRepo->getMatieres($filiere, $niveau, 2);

// =========================================================
// LECTURE COOKIES — notes sauvegardées
// =========================================================

$notesEnregistrees = [];

if (!empty($_COOKIE['insat_notes'])) {
    $decoded = json_decode(
        stripslashes($_COOKIE['insat_notes']),
        true
    );
    if (is_array($decoded)) {
        $notesEnregistrees = $decoded;
    }
}

// =========================================================
// FONCTIONS DE CALCUL
// =========================================================

function getNoteFromCookie(string $name, array $notes): ?float
{
    if (!isset($notes[$name]) || $notes[$name] === '') {
        return null;
    }

    $val = (float) $notes[$name];

    if ($val < 0)  $val = 0;
    if ($val > 20) $val = 20;

    return $val;
}

function calculerMatiere(?float $ds, ?float $tp, ?float $ex): ?float
{
    // DS + TP + EX
    if ($ds !== null && $tp !== null && $ex !== null) {
        return ($ds * 0.2) + ($tp * 0.2) + ($ex * 0.6);
    }

    // TP + EX
    if ($ds === null && $tp !== null && $ex !== null) {
        return ($tp * 0.3) + ($ex * 0.7);
    }

    // DS + EX
    if ($ds !== null && $tp === null && $ex !== null) {
        return ($ds * 0.3) + ($ex * 0.7);
    }

    // DS + TP sans exam
    if ($ds !== null && $tp !== null && $ex === null) {
        return ($ds * 0.4) + ($tp * 0.6);
    }

    // EX seul
    if ($ex !== null) return $ex;

    // DS seul
    if ($ds !== null) return $ds;

    // TP seul
    if ($tp !== null) return $tp;

    return null;
}

function calculerMoyenneSemestre(array $matieres, array $notes, object $repo): ?float
{
    $total      = 0;
    $totalCoeff = 0;

    foreach ($matieres as $matiere) {

        $nameClean = preg_replace('/[^A-Za-z0-9]/', '', $matiere['nom_matiere']);
        $coeff     = (float) $matiere['coefficient'];

        $ds = $repo->hasDS($matiere)   ? getNoteFromCookie($nameClean . 'DS', $notes) : null;
        $tp = $repo->hasTP($matiere)   ? getNoteFromCookie($nameClean . 'TP', $notes) : null;
        $ex = $repo->hasExam($matiere) ? getNoteFromCookie($nameClean . 'EX', $notes) : null;

        $moyenne = calculerMatiere($ds, $tp, $ex);

        if ($moyenne === null) continue;

        $total      += $moyenne * $coeff;
        $totalCoeff += $coeff;
    }

    return $totalCoeff === 0 ? null : $total / $totalCoeff;
}

// =========================================================
// CALCUL DES MOYENNES
// =========================================================

$moyenneS1      = calculerMoyenneSemestre($matieresS1, $notesEnregistrees, $matiereRepo);
$moyenneS2      = calculerMoyenneSemestre($matieresS2, $notesEnregistrees, $matiereRepo);

if ($moyenneS1 !== null && $moyenneS2 !== null) {
    $moyenneAnnuelle = ($moyenneS1 + $moyenneS2) / 2;
} elseif ($moyenneS1 !== null) {
    $moyenneAnnuelle = $moyenneS1;
} elseif ($moyenneS2 !== null) {
    $moyenneAnnuelle = $moyenneS2;
} else {
    $moyenneAnnuelle = null;
}

$afficherMoyenne = fn(?float $m) => $m !== null ? number_format($m, 2) : '--.--';
?>

<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        INSAT Grade Calculator
    </title>

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/notifications.css">

    <!-- CSS PAGE -->
    <link rel="stylesheet" href="/css/calculator.css">

</head>

<body>

<!-- ===================================================== -->
<!-- HEADER -->
<!-- ===================================================== -->

<?php require BASE_PATH . '/views/layouts/header.php'; ?>

<!-- ===================================================== -->
<!-- PAGE -->
<!-- ===================================================== -->

<div class="calculator-page">

    <div class="container">

        <div class="calculator-wrapper">

            <!-- ===================================================== -->
            <!-- HEADER -->
            <!-- ===================================================== -->

            <div class="calculator-header">

                <h1 class="calculator-title">
                    INSAT Grade Calculator
                </h1>

                <p class="calculator-subtitle">
                    Predict your average based on acquired or estimated grades
                </p>

                <div class="student-level">

                    <span class="student-level-label">
                        Currently showing :
                    </span>

                    <span class="student-level-value">
                        <?php echo htmlspecialchars($_SESSION['filiere'] ?? ''); ?>
                        <span class="student-level-sep">·</span>
                        <?php echo htmlspecialchars((string)$anneeAffichage); ?>
                    </span>

                </div>

            </div>

            <!-- ===================================================== -->
            <!-- FORM -->
            <!-- ===================================================== -->

            <form method="POST">

                <div class="semesters-grid">

                    <!-- ===================================================== -->
                    <!-- SEMESTRE 1 -->
                    <!-- ===================================================== -->

                    <div class="semester-card">

                        <h2>Semestre 1</h2>

                        <?php foreach ($matieresS1 as $matiere):

                            $nameClean = preg_replace('/[^A-Za-z0-9]/', '', $matiere['nom_matiere']);
                            $aUnDS     = $matiereRepo->hasDS($matiere);
                            $aUnExam   = $matiereRepo->hasExam($matiere);
                            $aUnTP     = $matiereRepo->hasTP($matiere);

                        ?>

                        <div class="subject-row">

                            <div class="subject-name">
                                <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                <span class="coeff">
                                    (x<?php echo number_format($matiere['coefficient'], 2); ?>)
                                </span>
                            </div>

                            <div
                                class="input-group"
                                data-coeff="<?php echo $matiere['coefficient']; ?>"
                            >

                                <?php if ($aUnDS): ?>
                                    <div class="input-field">
                                        <span class="input-field__label">DS</span>
                                        <input
                                            type="number"
                                            name="<?php echo $nameClean; ?>DS"
                                            class="matiere"
                                            min="0" max="20" step="0.25"
                                            placeholder="—"
                                            value="<?php echo htmlspecialchars($notesEnregistrees[$nameClean . 'DS'] ?? ''); ?>"
                                        >
                                    </div>
                                <?php endif; ?>

                                <?php if ($aUnTP): ?>
                                    <div class="input-field">
                                        <span class="input-field__label">TP</span>
                                        <input
                                            type="number"
                                            name="<?php echo $nameClean; ?>TP"
                                            class="matiere"
                                            min="0" max="20" step="0.25"
                                            placeholder="—"
                                            value="<?php echo htmlspecialchars($notesEnregistrees[$nameClean . 'TP'] ?? ''); ?>"
                                        >
                                    </div>
                                <?php endif; ?>

                                <?php if ($aUnExam): ?>
                                    <div class="input-field">
                                        <span class="input-field__label">Exam</span>
                                        <input
                                            type="number"
                                            name="<?php echo $nameClean; ?>EX"
                                            class="matiere"
                                            min="0" max="20" step="0.25"
                                            placeholder="—"
                                            value="<?php echo htmlspecialchars($notesEnregistrees[$nameClean . 'EX'] ?? ''); ?>"
                                        >
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    </div>

                    <!-- ===================================================== -->
                    <!-- SEMESTRE 2 -->
                    <!-- ===================================================== -->

                    <div class="semester-card">

                        <h2>Semestre 2</h2>

                        <?php foreach ($matieresS2 as $matiere):

                            $nameClean = preg_replace('/[^A-Za-z0-9]/', '', $matiere['nom_matiere']);
                            $aUnDS     = $matiereRepo->hasDS($matiere);
                            $aUnExam   = $matiereRepo->hasExam($matiere);
                            $aUnTP     = $matiereRepo->hasTP($matiere);

                        ?>

                        <div class="subject-row">

                            <div class="subject-name">
                                <?php echo htmlspecialchars($matiere['nom_matiere']); ?>
                                <span class="coeff">
                                    (x<?php echo number_format($matiere['coefficient'], 2); ?>)
                                </span>
                            </div>

                            <div
                                class="input-group"
                                data-coeff="<?php echo $matiere['coefficient']; ?>"
                            >

                                <?php if ($aUnDS): ?>
                                    <div class="input-field">
                                        <span class="input-field__label">DS</span>
                                        <input
                                            type="number"
                                            name="<?php echo $nameClean; ?>DS"
                                            class="matiere"
                                            min="0" max="20" step="0.25"
                                            placeholder="—"
                                            value="<?php echo htmlspecialchars($notesEnregistrees[$nameClean . 'DS'] ?? ''); ?>"
                                        >
                                    </div>
                                <?php endif; ?>

                                <?php if ($aUnTP): ?>
                                    <div class="input-field">
                                        <span class="input-field__label">TP</span>
                                        <input
                                            type="number"
                                            name="<?php echo $nameClean; ?>TP"
                                            class="matiere"
                                            min="0" max="20" step="0.25"
                                            placeholder="—"
                                            value="<?php echo htmlspecialchars($notesEnregistrees[$nameClean . 'TP'] ?? ''); ?>"
                                        >
                                    </div>
                                <?php endif; ?>

                                <?php if ($aUnExam): ?>
                                    <div class="input-field">
                                        <span class="input-field__label">Exam</span>
                                        <input
                                            type="number"
                                            name="<?php echo $nameClean; ?>EX"
                                            class="matiere"
                                            min="0" max="20" step="0.25"
                                            placeholder="—"
                                            value="<?php echo htmlspecialchars($notesEnregistrees[$nameClean . 'EX'] ?? ''); ?>"
                                        >
                                    </div>
                                <?php endif; ?>

                            </div>

                        </div>

                        <?php endforeach; ?>

                    </div>

                </div>

                <!-- ===================================================== -->
                <!-- RESULTATS -->
                <!-- ===================================================== -->

                <div class="results-card">

                    <h2>Calculated Results</h2>

                    <div class="results-grid">

                        <div class="result-box">
                            <h3>Semestre 1</h3>
                            <span id="average1">
                                <?php echo $afficherMoyenne($moyenneS1); ?>
                            </span>
                        </div>

                        <div class="result-box">
                            <h3>Semestre 2</h3>
                            <span id="average2">
                                <?php echo $afficherMoyenne($moyenneS2); ?>
                            </span>
                        </div>

                        <div class="result-box total">
                            <h3>Moyenne Annuelle</h3>
                            <span id="year-average">
                                <?php echo $afficherMoyenne($moyenneAnnuelle); ?>
                            </span>
                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>

</div>

<!-- =========================================================
     SCRIPT — sauvegarde cookie + recalcul dynamique côté client
     Le calcul principal est fait en PHP au chargement.
     Le JS recalcule en temps réel pendant la saisie
     et sauvegarde dans le cookie insat_notes.
========================================================= -->
<script src="/js/calculator.js"></script>

</body>
</html>