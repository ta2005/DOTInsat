<?php
// config/storage.php — Couche de persistance en session (simulée)
// À remplacer ultérieurement par une vraie couche base de données.

/**
 * Initialise le stockage en session si ce n'est pas déjà fait.
 * Charge les données de départ depuis workflow.php.
 */
function storage_init(): void {
    if (!isset($_SESSION['reclamations'])) {
        $init = require __DIR__ . '/workflow.php';
        $_SESSION['reclamations']  = $init['reclamations'];
        $_SESSION['notifications'] = $init['notifications'];
        $_SESSION['next_id']       = 3; // prochain ID libre
    }
    if (!isset($_SESSION['demandes'])) {
        $_SESSION['demandes']     = require __DIR__ . '/demandes_init.php';
        $_SESSION['next_dem_id']  = 4; // prochain ID libre pour les demandes
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// DEMANDES
// ─────────────────────────────────────────────────────────────────────────────

/** Retourne toutes les demandes. */
function demandes_all(): array {
    return $_SESSION['demandes'] ?? [];
}

/** Retourne une demande par son ID, ou null si introuvable. */
function demande_get(int $id): ?array {
    foreach ($_SESSION['demandes'] as $d) {
        if ($d['id'] === $id) return $d;
    }
    return null;
}

/** Ajoute une nouvelle demande et retourne son ID généré. */
function demande_ajouter(array $data): int {
    $id = $_SESSION['next_dem_id']++;
    $data['id'] = $id;
    $_SESSION['demandes'][] = $data;
    return $id;
}

/** Met à jour les champs d'une demande existante. */
function demande_update(int $id, array $changes): void {
    foreach ($_SESSION['demandes'] as &$d) {
        if ($d['id'] === $id) {
            foreach ($changes as $k => $v) {
                $d[$k] = $v;
            }
            break;
        }
    }
    unset($d);
}

// ─────────────────────────────────────────────────────────────────────────────
// RÉCLAMATIONS
// ─────────────────────────────────────────────────────────────────────────────

/** Retourne toutes les réclamations. */
function reclamations_all(): array {
    return $_SESSION['reclamations'] ?? [];
}

/** Retourne une réclamation par son ID, ou null si introuvable. */
function reclamation_get(int $id): ?array {
    foreach ($_SESSION['reclamations'] as $r) {
        if ($r['id'] === $id) return $r;
    }
    return null;
}

/** Ajoute une nouvelle réclamation et retourne son ID généré. */
function reclamation_ajouter(array $data): int {
    $id = $_SESSION['next_id']++;
    $data['id'] = $id;
    $_SESSION['reclamations'][] = $data;
    return $id;
}

/** Met à jour les champs d'une réclamation existante. */
function reclamation_update(int $id, array $changes): void {
    foreach ($_SESSION['reclamations'] as &$r) {
        if ($r['id'] === $id) {
            foreach ($changes as $k => $v) {
                $r[$k] = $v;
            }
            break;
        }
    }
    unset($r); // éviter la corruption par référence persistante
}

// ─────────────────────────────────────────────────────────────────────────────
// NOTIFICATIONS
// ─────────────────────────────────────────────────────────────────────────────

/**
 * Ajoute une notification pour un destinataire donné.
 * Les notifications expirent automatiquement après 48 heures.
 *
 * @param string $destinataire  Identifiant du destinataire : 'etudiant_1', 'admin', 'prof_algo', etc.
 * @param string $message       Texte de la notification.
 * @param int    $reclamation_id ID de la réclamation concernée.
 */
function notification_ajouter(string $destinataire, string $message, int $reclamation_id): void {
    $_SESSION['notifications'][] = [
        'id'             => uniqid(),
        'destinataire'   => $destinataire,
        'message'        => $message,
        'reclamation_id' => $reclamation_id,
        'lu'             => false,
        'date'           => date('Y-m-d H:i'),
        'expiration'     => date('Y-m-d H:i', strtotime('+48 hours')),
    ];
}

/**
 * Retourne les notifications non expirées d'un destinataire,
 * triées de la plus récente à la plus ancienne.
 */
function notifications_pour(string $destinataire): array {
    $now    = date('Y-m-d H:i');
    $notifs = array_values(array_filter(
        $_SESSION['notifications'] ?? [],
        fn($n) => $n['destinataire'] === $destinataire && $n['expiration'] > $now
    ));
    usort($notifs, fn($a, $b) => strcmp($b['date'], $a['date']));
    return $notifs;
}

/** Marque une notification comme lue par son ID unique. */
function notification_marquer_lue(string $id): void {
    foreach ($_SESSION['notifications'] as &$n) {
        if ($n['id'] === $id) {
            $n['lu'] = true;
            break;
        }
    }
    unset($n);
}
