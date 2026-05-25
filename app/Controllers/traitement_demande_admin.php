<?php
// traitement_demande_admin.php — Traitement des actions admin sur les demandes
session_start();
require_once __DIR__ . '/config/storage.php';
storage_init();

$action       = trim($_POST['action'] ?? '');
$id           = (int) ($_POST['id']   ?? 0);
$raison_refus = trim($_POST['raison_refus']    ?? '');
$reponse      = trim($_POST['reponse_admin']   ?? '');

$demande = demande_get($id);

if (!$demande) {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Demande introuvable.'];
    header('Location: admin_demandes.php');
    exit;
}

if ($demande['statut'] !== 'en_attente') {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Cette demande a déjà été traitée.'];
    header('Location: admin_demandes.php');
    exit;
}

if ($action === 'approuver') {
    demande_update($id, [
        'statut'        => 'approuve',
        'reponse_admin' => $reponse ?: null,
        'date_traitement' => date('Y-m-d H:i'),
    ]);
    // Notifier l'étudiant
    notification_ajouter(
        'etudiant_' . ($demande['num'] ?? $id),
        'Votre demande « ' . $demande['type_label'] . ' » a été approuvée.' . ($reponse ? ' Message : ' . $reponse : ''),
        $id
    );
    $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Demande approuvée avec succès.'];

} elseif ($action === 'refuser') {
    if (!$raison_refus) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'La raison du refus est obligatoire.'];
        header('Location: admin_demandes.php');
        exit;
    }
    demande_update($id, [
        'statut'          => 'refuse',
        'raison_refus'    => $raison_refus,
        'date_traitement' => date('Y-m-d H:i'),
    ]);
    // Notifier l'étudiant
    notification_ajouter(
        'etudiant_' . ($demande['num'] ?? $id),
        'Votre demande « ' . $demande['type_label'] . ' » a été refusée. Raison : ' . $raison_refus,
        $id
    );
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Demande refusée.'];

} else {
    $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Action invalide.'];
}

header('Location: admin_demandes.php');
exit;
