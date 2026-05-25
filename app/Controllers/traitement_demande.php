<?php
// traitement_demande.php — Traitement du formulaire de demande administrative
session_start();

$nom         = trim($_POST['nom']             ?? '');
$prenom      = trim($_POST['prenom']          ?? '');
$num         = trim($_POST['num_inscription'] ?? '');
$type        = trim($_POST['type_demande']    ?? '');
$autre_type  = trim($_POST['autre_type']      ?? '');
$commentaire = trim($_POST['commentaire']     ?? '');

// Validation des champs obligatoires
if (!$nom || !$prenom || !$num || !$type) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tous les champs obligatoires doivent être remplis.'];
    header('Location: demande.php');
    exit;
}

// Si type "autre", le champ de précision est obligatoire
if ($type === 'autre' && !$autre_type) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Veuillez préciser le type de votre demande.'];
    header('Location: demande.php');
    exit;
}

// Types de demande valides
$types_valides = ['stage', 'attestation', 'salle', 'autre'];
if (!in_array($type, $types_valides)) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Type de demande invalide.'];
    header('Location: demande.php');
    exit;
}

// Ici : sauvegarder en base de données ou envoyer un e-mail
// À implémenter selon l'infrastructure cible.
// Exemple : Demande::create([...]) avec un ORM, ou insertion PDO directe.

$_SESSION['flash'] = ['type' => 'success', 'msg' => 'Votre demande a bien été envoyée. Vous serez contacté prochainement.'];
header('Location: demande.php');
exit;
