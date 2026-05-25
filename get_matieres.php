<?php
require_once 'db.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['filiere']) && isset($_GET['niveau'])) {
    $filiere = $_GET['filiere'];
    $niveau = intval($_GET['niveau']); 

    try {
        
        $stmt = $db_cnx->prepare("SELECT semestre, nom_matiere, coefficient FROM matieres WHERE filiere = :filiere AND niveau = :niveau ORDER BY semestre ASC, id ASC");
        
        $stmt->execute([
            'filiere' => $filiere,
            'niveau' => $niveau
        ]);
    
        $resultats = $stmt->fetchAll();

        echo json_encode($resultats);

    } catch (PDOException $e) {
        echo json_encode(['erreur' => 'Erreur de base de données : ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['erreur' => 'Paramètres filiere et niveau manquants.']);
}
?>
