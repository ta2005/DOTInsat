<?php
// traitement_reclamation.php — Traitement centralisé de toutes les actions du workflow réclamation
session_start();
require_once __DIR__ . '/config/storage.php';
storage_init();

$action = $_POST['action'] ?? '';


if ($action === 'soumettre') {
    $nom         = trim($_POST['nom']             ?? '');
    $prenom      = trim($_POST['prenom']          ?? '');
    $num         = trim($_POST['num_inscription'] ?? '');
    $matiere_id  = trim($_POST['matiere']         ?? '');
    $type_eval   = trim($_POST['type_eval']       ?? '');
    $commentaire = trim($_POST['commentaire']     ?? '');

    if (!$nom || !$prenom || !$num || !$matiere_id || !$type_eval || !$commentaire) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Tous les champs obligatoires doivent être remplis.'];
        header('Location: reclamation.php');
        exit;
    }

    // Récupérer les infos de la matière depuis la config
    $reclamationConfig = require __DIR__ . '/config/reclamation.php';
    $matiereInfo = null;
    foreach ($reclamationConfig['matieres'] as $m) {
        if ($m['id'] === $matiere_id) { $matiereInfo = $m; break; }
    }

    if (!$matiereInfo) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Matière invalide.'];
        header('Location: reclamation.php');
        exit;
    }

    $type_eval_labels = ['ds' => 'DS', 'examen' => 'Examen', 'tp' => 'TP'];
    $note_actuelle    = $matiereInfo[$type_eval] ?? null;

    $id = reclamation_ajouter([
        'etudiant_id'      => 'etudiant_1', // à remplacer par $_SESSION['user_id'] avec une vraie auth
        'nom'              => $nom,
        'prenom'           => $prenom,
        'num'              => $num,
        'matiere_id'       => $matiere_id,
        'matiere_nom'      => $matiereInfo['nom'],
        'type_eval'        => $type_eval,
        'type_eval_label'  => $type_eval_labels[$type_eval] ?? $type_eval,
        'note_actuelle'    => $note_actuelle,
        'note_nouvelle'    => null,
        'prof_nom'         => $matiereInfo['prof'],
        'commentaire'      => $commentaire,
        'statut'           => 'en_attente',
        'date_soumission'  => date('Y-m-d H:i'),
        'raison_refus'     => null,
    ]);

    // Notifier l'administrateur
    notification_ajouter('admin', "Nouvelle réclamation #{$id} de {$prenom} {$nom} — {$matiereInfo['nom']}", $id);

    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Réclamation envoyée avec succès. Vous serez notifié du suivi.'];
    header('Location: reclamation.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN — Approuver et transmettre au professeur
// ─────────────────────────────────────────────────────────────────────────────
if ($action === 'admin_approuver') {
    $id = (int)($_POST['id'] ?? 0);
    $r  = reclamation_get($id);

    if (!$r || $r['statut'] !== 'en_attente') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Réclamation introuvable ou déjà traitée.'];
        header('Location: admin_reclamations.php');
        exit;
    }

    reclamation_update($id, ['statut' => 'approuve_admin']);

    // Notifier le professeur (identifiant basé sur la matière)
    $prof_dest = 'prof_' . $r['matiere_id'];
    notification_ajouter(
        $prof_dest,
        "Réclamation #{$id} de {$r['prenom']} {$r['nom']} transmise par l'admin — {$r['matiere_nom']}",
        $id
    );
    // Notifier l'étudiant
    notification_ajouter(
        $r['etudiant_id'],
        "Votre réclamation #{$id} ({$r['matiere_nom']}) a été approuvée par l'administration et transmise au professeur.",
        $id
    );

    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Réclamation #{$id} approuvée et transmise au professeur."];
    header('Location: admin_reclamations.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN — Refuser la réclamation
// ─────────────────────────────────────────────────────────────────────────────
if ($action === 'admin_refuser') {
    $id     = (int)($_POST['id']    ?? 0);
    $raison = trim($_POST['raison'] ?? '');
    $r      = reclamation_get($id);

    if (!$r || $r['statut'] !== 'en_attente') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Réclamation introuvable ou déjà traitée.'];
        header('Location: admin_reclamations.php');
        exit;
    }
    if (!$raison) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'La raison du refus est obligatoire.'];
        header('Location: admin_reclamations.php');
        exit;
    }

    reclamation_update($id, ['statut' => 'refuse_admin', 'raison_refus' => $raison]);

    // Notifier l'étudiant
    notification_ajouter(
        $r['etudiant_id'],
        "Votre réclamation #{$id} ({$r['matiere_nom']}) a été refusée par l'administration. Motif : {$raison}",
        $id
    );

    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Réclamation #{$id} refusée."];
    header('Location: admin_reclamations.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// PROFESSEUR — Accepter et modifier la note
// ─────────────────────────────────────────────────────────────────────────────
if ($action === 'prof_approuver') {
    $id            = (int)($_POST['id']             ?? 0);
    $nouvelle_note = $_POST['nouvelle_note'] ?? '';
    $r             = reclamation_get($id);

    if (!$r || $r['statut'] !== 'approuve_admin') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Réclamation introuvable ou non transmise.'];
        header('Location: prof_reclamations.php');
        exit;
    }

    $note = filter_var($nouvelle_note, FILTER_VALIDATE_FLOAT);
    if ($note === false || $note < 0 || $note > 20) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Note invalide (doit être entre 0 et 20).'];
        header('Location: prof_reclamations.php');
        exit;
    }

    reclamation_update($id, [
        'statut'       => 'approuve_prof',
        'note_nouvelle' => $note,
    ]);

    // Notifier l'étudiant
    notification_ajouter(
        $r['etudiant_id'],
        "Votre réclamation #{$id} ({$r['matiere_nom']}) a été acceptée. Nouvelle note : {$note}/20.",
        $id
    );

    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Note modifiée avec succès : {$note}/20."];
    header('Location: prof_reclamations.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// PROFESSEUR — Refuser la réclamation
// ─────────────────────────────────────────────────────────────────────────────
if ($action === 'prof_refuser') {
    $id     = (int)($_POST['id']    ?? 0);
    $raison = trim($_POST['raison'] ?? '');
    $r      = reclamation_get($id);

    if (!$r || $r['statut'] !== 'approuve_admin') {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Réclamation introuvable ou non transmise.'];
        header('Location: prof_reclamations.php');
        exit;
    }
    if (!$raison) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'La raison du refus est obligatoire.'];
        header('Location: prof_reclamations.php');
        exit;
    }

    reclamation_update($id, ['statut' => 'refuse_prof', 'raison_refus' => $raison]);

    // Notifier l'étudiant
    notification_ajouter(
        $r['etudiant_id'],
        "Votre réclamation #{$id} ({$r['matiere_nom']}) a été refusée par le professeur. Motif : {$raison}",
        $id
    );

    $_SESSION['flash'] = ['type' => 'success', 'msg' => "Réclamation #{$id} refusée."];
    header('Location: prof_reclamations.php');
    exit;
}

// ─────────────────────────────────────────────────────────────────────────────
// Action inconnue → redirection sécurisée
// ─────────────────────────────────────────────────────────────────────────────
$_SESSION['flash'] = ['type' => 'error', 'msg' => 'Action inconnue.'];
header('Location: index.php');
exit;
