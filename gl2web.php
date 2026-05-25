<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=dotinsat;charset=utf8', 'root', '');
    //$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion : ' . $e->getMessage());
}

// Nrécupéri matiérét S1
$reqS1 = $bdd->prepare("SELECT nom_matiere, coefficient FROM matieres WHERE filiere = 'GL' AND niveau = 2 AND semestre = 1");
$reqS1->execute();
$matieresS1 = $reqS1->fetchAll(PDO::FETCH_ASSOC);

// Nrécupéri matiérét S2
$reqS2 = $bdd->prepare("SELECT nom_matiere, coefficient FROM matieres WHERE filiere = 'GL' AND niveau = 2 AND semestre = 2");
$reqS2->execute();
$matieresS2 = $reqS2->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>INSAT Grade Calculator - GL2</title>
    <link rel="stylesheet" href="style.css">
    <script src="selection.js" defer></script>
    <script src="calculator.js" defer></script>
</head>
<body>
    <header>INSAT Grade Calculator</header>
    <h3><i>Predict your average based on acquired or estimated grades</i></h3>
    <hr class="hr">

    <fieldset>
        <legend>Start by choosing your major:</legend>
        
        Study Year: 
        <select name="StudyYear" id="StudyYear">
            <option value="none">Select Year First</option>
            <option value="1">1st</option>
            <option value="2">2nd</option>
            <option value="3">3rd</option>
            <option value="4">4th</option>
            <option value="5">5th</option>
        </select>
        
        Study Field: 
        <select name="Study Field" id="StudyField">
            <option value="none">Select Year First</option>
        </select>
        
        <button class="load" onclick="load()">Load Subjects</button>
    </fieldset>

    <div id="showing">Currently showing: GL2</div>
    <?php
        $sansTP = ['Probabilités et Statistiques', 'Mathématiques du signal', 'Systèmes d\'exploitation', 'Architecture des ordinateurs', 'Techniques de transmission', 'Anglais', 'Gestion', 'Algèbre','Analyse Mathématique','Anglais','Français','Droit de l\'homme','Comptabilité'];
        $sansDS = ['Atelier Python Avancé', 'Atelier C++','Atelier Java Avancé','Développement Web','Applications réparties','Système d\'exploitation UNIX']; 
        $sansEx = []; 
    ?>

    <form method="POST" action="">
        
        <fieldset class="subjects">
        <legend>Semestre 1</legend>
        
        <?php foreach ($matieresS1 as $index => $matiere): 
            $nameClean = str_replace(' ', '', $matiere['nom_matiere']);
            
            // On vérifie pour chaque matière chnia devoiratha
            $aUnTP = !in_array($matiere['nom_matiere'], $sansTP);
            $aUnDS = !in_array($matiere['nom_matiere'], $sansDS);
            $aUnEx = !in_array($matiere['nom_matiere'], $sansEx);
        ?>
        
            <div class="subject-row">
                <label><?php echo htmlspecialchars($matiere['nom_matiere']); ?> (x<?php echo $matiere['coefficient']; ?>):</label>
                
                <div class="input-group" data-coeff="<?php echo $matiere['coefficient']; ?>">
                    
                    <?php if ($aUnDS): ?>
                        <input type="number" name="<?php echo $nameClean; ?>DS" class="matiere" min="0" max="20" step="0.25" placeholder="DS">
                    <?php else: ?>
                        <input type="number" placeholder="DS" disabled style="background-color: #ddd; cursor: not-allowed; opacity: 0.6;">
                    <?php endif; ?>

                    <?php if ($aUnEx): ?>
                        <input type="number" name="<?php echo $nameClean; ?>Ex" class="matiere" min="0" max="20" step="0.25" placeholder="Ex">
                    <?php else: ?>
                        <input type="number" placeholder="Ex" disabled style="background-color: #ddd; cursor: not-allowed; opacity: 0.6;">
                    <?php endif; ?>
                    
                    <?php if ($aUnTP): ?>
                        <input type="number" name="<?php echo $nameClean; ?>TP" class="matiere" min="0" max="20" step="0.25" placeholder="TP">
                    <?php else: ?>
                        <input type="number" placeholder="TP" disabled style="background-color: #ddd; cursor: not-allowed; opacity: 0.6;">
                    <?php endif; ?>

                </div>
            </div>
            
        <?php endforeach; ?>
    
        </fieldset>
     <fieldset class="subjects">
    <legend>Semestre 2</legend>
    
    <?php foreach ($matieresS2 as $index => $matiere): 
        $nameClean = str_replace(' ', '', $matiere['nom_matiere']);
        
        $aUnTP = !in_array($matiere['nom_matiere'], $sansTP);
        $aUnDS = !in_array($matiere['nom_matiere'], $sansDS);
        $aUnEx = !in_array($matiere['nom_matiere'], $sansEx);
    ?>
    
        <div class="subject-row">
            <label><?php echo htmlspecialchars($matiere['nom_matiere']); ?> (x<?php echo $matiere['coefficient']; ?>):</label>
            
            <div class="input-group" data-coeff="<?php echo $matiere['coefficient']; ?>">
                
                <?php if ($aUnDS): ?>
                    <input type="number" name="<?php echo $nameClean; ?>DS" class="matiere" min="0" max="20" step="0.25" placeholder="DS">
                <?php else: ?>
                    <input type="number" placeholder="DS" disabled style="background-color: #ddd; cursor: not-allowed; opacity: 0.6;">
                <?php endif; ?>

                <?php if ($aUnEx): ?>
                    <input type="number" name="<?php echo $nameClean; ?>Ex" class="matiere" min="0" max="20" step="0.25" placeholder="Ex">
                <?php else: ?>
                    <input type="number" placeholder="Ex" disabled style="background-color: #ddd; cursor: not-allowed; opacity: 0.6;">
                <?php endif; ?>
                
                <?php if ($aUnTP): ?>
                    <input type="number" name="<?php echo $nameClean; ?>TP" class="matiere" min="0" max="20" step="0.25" placeholder="TP">
                <?php else: ?>
                    <input type="number" placeholder="TP" disabled style="background-color: #ddd; cursor: not-allowed; opacity: 0.6;">
                <?php endif; ?>

            </div>
        </div>
        
        <?php endforeach; ?>
        </fieldset>
        <fieldset style="margin-top: 20px;">
            <legend><i>Calculated Results:</i></legend>
            <div style="display: flex; justify-content: space-around; text-align: center; font-size: 18px; font-weight: bold;">
                <div>
                    Semestre 1 <br>
                    <span id="average1">--.--</span>
                </div>
                <div style="border-left: 1px solid #ccc; padding-left: 20px;">
                    Semestre 2 <br>
                    <span id="average2">--.--</span>
                </div>
                <div style="border-left: 1px solid #ccc; padding-left: 20px; font-size: 22px;">
                    Moyenne Annuelle <br>
                    <span id="year-average">--.--</span>
                </div>
            </div>
        </fieldset>
    </form>

</body>
</html>