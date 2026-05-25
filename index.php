<?php
require_once 'db.php'; 

$filiere = $_GET['StudyField'] ?? $_POST['filiere'] ?? '';
$niveau  = $_GET['StudyYear']  ?? $_POST['niveau']  ?? '';

$matieres = [];
if (!empty($filiere) && $filiere !== 'none' && !empty($niveau)) {
    $req = $db_cnx->prepare("SELECT semestre, nom_matiere, coefficient FROM matieres WHERE filiere = :filiere AND niveau = :niveau ORDER BY semestre ASC, id ASC");
    $req->execute(array('filiere' => $filiere, 'niveau' => $niveau));
    $matieres = $req->fetchAll(PDO::FETCH_ASSOC);
}

$moyenne_s1 = null;
$moyenne_s2 = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['calculer'])) {
    $total_score_s1 = 0;
    $total_coeff_s1 = 0;
    $total_score_s2 = 0; 
    $total_coeff_s2 = 0;

    foreach ($matieres as $index => $matiere) {
        $coeff = (float)$matiere['coefficient'];
        $semestre = (int)$matiere['semestre'];
        if (isset($_POST['ex'][$index]) && $_POST['ex'][$index] !== '') {
            $ex = (float)$_POST['ex'][$index];
            $ds = (isset($_POST['ds'][$index]) && $_POST['ds'][$index] !== '') ? (float)$_POST['ds'][$index] : null;
            $tp = (isset($_POST['tp'][$index]) && $_POST['tp'][$index] !== '') ? (float)$_POST['tp'][$index] : null;

            $moy_matiere = 0;
            if ($ds !== null && $tp !== null) {
                $moy_matiere = ($ds * 0.2) + ($tp * 0.2) + ($ex * 0.6);
            } elseif ($ds === null && $tp !== null) {
                $moy_matiere = ($tp * 0.3) + ($ex * 0.7);
            } elseif ($ds !== null && $tp === null) {
                $moy_matiere = ($ds * 0.3) + ($ex * 0.7);
            } else {
                $moy_matiere = $ex;
            }

            if ($semestre === 1) {
                $total_score_s1 += ($moy_matiere * $coeff);
                $total_coeff_s1 += $coeff;
            } else {
                $total_score_s2 += ($moy_matiere * $coeff);
                $total_coeff_s2 += $coeff;
            }
        }
    }

    if ($total_coeff_s1 > 0) $moyenne_s1 = $total_score_s1 / $total_coeff_s1;
    if ($total_coeff_s2 > 0) $moyenne_s2 = $total_score_s2 / $total_coeff_s2;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>INSAT Grade Calculator</title>
    <link rel="stylesheet" href="style.css">
    <script src="interactivite.js" defer></script> </head>
<body>
    <header>INSAT Grade Calculator</header>
    <h3><i>Predict your average based on acquired or estimated grades</i></h3>
    <hr class="hr">
    
    <form method="GET" action="index.php">
        <fieldset>
            <legend>Start by choosing your major:</legend>
            Study Year: 
            <select name="StudyYear" id="StudyYear" onchange="updateSelection()">
                <option value="none">Select Year First</option>
                <option value="1" <?php if($niveau=='1') echo 'selected'; ?>>1st</option>
                <option value="2" <?php if($niveau=='2') echo 'selected'; ?>>2nd</option>
                <option value="3" <?php if($niveau=='3') echo 'selected'; ?>>3rd</option>
                <option value="4" <?php if($niveau=='4') echo 'selected'; ?>>4th</option>
                <option value="5" <?php if($niveau=='5') echo 'selected'; ?>>5th</option>
            </select>
            
            Study Field: 
            <select name="StudyField" id="StudyField">
                <option value="<?php echo htmlspecialchars($filiere); ?>"><?php echo empty($filiere) ? 'Select Field' : htmlspecialchars($filiere); ?></option>
                </select>
            
            <button type="submit" class="load">Load Subjects</button>
        </fieldset>
    </form>

    <?php if (!empty($matieres)): ?>
        <div id="showing">Currently showing: <?php echo htmlspecialchars($filiere . $niveau); ?></div>
        
        <form method="POST" action="index.php">
            <input type="hidden" name="filiere" value="<?php echo htmlspecialchars($filiere); ?>">
            <input type="hidden" name="niveau" value="<?php echo htmlspecialchars($niveau); ?>">

            <fieldset class="subjects">
                <legend>Semester 1 :</legend>
                <div class="grid-container">
                    <div class="header-row" style="grid-column: 2; margin-left:120px;">
                        <span class="header-title">DS</span>
                        <span class="header-title">EX</span>
                        <span class="header-title">TP</span>
                    </div>
                    <?php 
                    foreach ($matieres as $index => $m): 
                        if ($m['semestre'] == 1):
                    ?>
                        <label><?php echo htmlspecialchars($m['nom_matiere']); ?> (Coef <?php echo $m['coefficient']; ?>):</label>
                        <div class="input-group">
                            <input type="number" name="ds[<?php echo $index; ?>]" min="0" max="20" step="0.25" value="<?php echo isset($_POST['ds'][$index]) ? htmlspecialchars($_POST['ds'][$index]) : ''; ?>">
                            <input type="number" name="ex[<?php echo $index; ?>]" min="0" max="20" step="0.25" value="<?php echo isset($_POST['ex'][$index]) ? htmlspecialchars($_POST['ex'][$index]) : ''; ?>">
                            <input type="number" name="tp[<?php echo $index; ?>]" min="0" max="20" step="0.25" value="<?php echo isset($_POST['tp'][$index]) ? htmlspecialchars($_POST['tp'][$index]) : ''; ?>">
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                <?php if($moyenne_s1 !== null): ?>
                    <h3 style="color:red;">Average S1 : <?php echo number_format($moyenne_s1, 2); ?></h3>
                <?php endif; ?>
            </fieldset>

            <fieldset class="subjects">
                <legend>Semester 2 :</legend>
                <div class="grid-container">
                    <div class="header-row" style="grid-column: 2; margin-left:120px;">
                        <span class="header-title">DS</span>
                        <span class="header-title">EX</span>
                        <span class="header-title">TP</span>
                    </div>
                    <?php 
                    foreach ($matieres as $index => $m): 
                        if ($m['semestre'] == 2):
                    ?>
                        <label><?php echo htmlspecialchars($m['nom_matiere']); ?> (Coef <?php echo $m['coefficient']; ?>):</label>
                        <div class="input-group">
                            <input type="number" name="ds[<?php echo $index; ?>]" min="0" max="20" step="0.25" value="<?php echo isset($_POST['ds'][$index]) ? htmlspecialchars($_POST['ds'][$index]) : ''; ?>">
                            <input type="number" name="ex[<?php echo $index; ?>]" min="0" max="20" step="0.25" value="<?php echo isset($_POST['ex'][$index]) ? htmlspecialchars($_POST['ex'][$index]) : ''; ?>">
                            <input type="number" name="tp[<?php echo $index; ?>]" min="0" max="20" step="0.25" value="<?php echo isset($_POST['tp'][$index]) ? htmlspecialchars($_POST['tp'][$index]) : ''; ?>">
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                <?php if($moyenne_s2 !== null): ?>
                    <h3 style="color:red;">Average S2 : <?php echo number_format($moyenne_s2, 2); ?></h3>
                <?php endif; ?>
            </fieldset>

            <div style="text-align: center; margin: 20px;">
                <button type="submit" name="calculer" style="padding: 10px 20px; font-size: 18px; cursor: pointer;">Calculer mes moyennes</button>
            </div>
        </form>
    <?php endif; ?>

    <script>
        function updateSelection() {
            const year = document.getElementById('StudyYear').value;
            const field = document.getElementById('StudyField');
            if (year === "1") {
                field.innerHTML = '<option value="MPI">MPI</option><option value="CBA">CBA</option>';
            } else if (["2", "3", "4", "5"].includes(year)) {
                field.innerHTML = '<option value="GL">GL</option><option value="RT">RT</option><option value="IMI">IMI</option><option value="IIA">IIA</option><option value="CH">CH</option><option value="BIO">BIO</option>';
            }
        }
    </script>
</body>
</html>
