<?php


function storage_init(): void {
    $pdo = get_pdo();
    if ($pdo) return;

    if (!isset($_SESSION['reclamations'])) {
        $init = require __DIR__ . '/workflow.php';
        $_SESSION['reclamations']  = $init['reclamations'] ?? [];
        $_SESSION['notifications'] = $init['notifications'] ?? [];
        $_SESSION['next_id']       = count($_SESSION['reclamations']) + 1;
    }
    if (!isset($_SESSION['demandes'])) {
        $init = require __DIR__ . '/demandes_init.php';
        $_SESSION['demandes']    = $init ?? [];
        $_SESSION['next_dem_id'] = count($_SESSION['demandes']) + 1;
    }
}

function demandes_all(): array {
    $pdo = get_pdo();
    if ($pdo) {
        return $pdo->query("
            SELECT d.id, u.nom, u.prenom, d.message AS commentaire,
                   d.type::TEXT, d.type::TEXT AS type_label,
                   d.statut::TEXT AS statut, d.date_creation AS date_soumission,
                   NULL AS raison_refus, NULL AS reponse_admin
            FROM demande d JOIN users u ON u.id = d.user_id
            ORDER BY d.date_creation DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
    return $_SESSION['demandes'] ?? [];
}

function demande_get(int $id): ?array {
    $pdo = get_pdo();
    if ($pdo) {
        $stmt = $pdo->prepare("
            SELECT d.id, u.nom, u.prenom, d.message AS commentaire,
                   d.type::TEXT, d.type::TEXT AS type_label,
                   d.statut::TEXT AS statut, d.date_creation AS date_soumission,
                   NULL AS raison_refus, NULL AS reponse_admin
            FROM demande d JOIN users u ON u.id = d.user_id
            WHERE d.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    foreach ($_SESSION['demandes'] as $d) {
        if ($d['id'] === $id) return $d;
    }
    return null;
}

function demande_ajouter(array $data): int {
    $pdo = get_pdo();
    if ($pdo) {
        $typeMap = [
            'attestation' => 'ATTESTATION_DE_INSCRIPTION',
            'stage'       => 'FEUILLES_DE_STAGE',
            'salle'       => 'ATTESTATION_DE_PRESENCE',
            'autre'       => 'FEUILLES_DE_NOTES',
        ];
        $stmt = $pdo->prepare("
            INSERT INTO demande (message, type, statut, user_id, admin_id)
            VALUES (:message, :type, 'EN_ATTENTE', :user_id, :admin_id)
            RETURNING id
        ");
        $stmt->execute([
            ':message'  => $data['commentaire'] ?? '',
            ':type'     => $typeMap[$data['type']] ?? 'FEUILLES_DE_NOTES',
            ':user_id'  => $_SESSION['user_id'] ?? 1,
            ':admin_id' => 1,
        ]);
        return (int)$stmt->fetchColumn();
    }
    $id = $_SESSION['next_dem_id']++;
    $data['id'] = $id;
    $_SESSION['demandes'][] = $data;
    return $id;
}

function demande_update(int $id, array $changes): void {
    $pdo = get_pdo();
    if ($pdo) {
        $statutMap = ['approuve' => 'ACCEPTEE', 'refuse' => 'REFUSEE'];
        if (isset($changes['statut'])) {
            $s = $statutMap[$changes['statut']] ?? strtoupper($changes['statut']);
            $pdo->prepare("UPDATE demande SET statut = ? WHERE id = ?")->execute([$s, $id]);
        }
        return;
    }
    foreach ($_SESSION['demandes'] as &$d) {
        if ($d['id'] === $id) {
            foreach ($changes as $k => $v) $d[$k] = $v;
            break;
        }
    }
    unset($d);
}

function reclamations_all(): array {
    $pdo = get_pdo();
    if ($pdo) {
        return $pdo->query("
            SELECT r.id, r.etudiant_id, u.nom, u.prenom, '' AS num,
                   e.id AS enseignement_id, e.nom AS matiere_nom,
                   r.type_controle::TEXT AS type_eval,
                   r.type_controle::TEXT AS type_eval_label,
                   c.note AS note_actuelle, NULL::NUMERIC AS note_nouvelle,
                   pu.nom || ' ' || pu.prenom AS prof_nom,
                   r.message AS commentaire,
                   r.statut::TEXT AS statut,
                   r.date_creation AS date_soumission,
                   NULL AS raison_refus
            FROM reclamation r
            JOIN etudiant et  ON et.id = r.etudiant_id
            JOIN users u      ON u.id  = et.id
            JOIN enseignement e  ON e.id = r.enseignement_id
            JOIN professeur p    ON p.id = e.professeur_id
            JOIN users pu        ON pu.id = p.id
            LEFT JOIN controle c ON c.enseignement_id = e.id
                                AND c.type::TEXT = r.type_controle::TEXT
            ORDER BY r.date_creation DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
    return $_SESSION['reclamations'] ?? [];
}

function reclamation_get(int $id): ?array {
    $pdo = get_pdo();
    if ($pdo) {
        $stmt = $pdo->prepare("
            SELECT r.id, r.etudiant_id, u.nom, u.prenom, '' AS num,
                   e.id AS enseignement_id, e.nom AS matiere_nom,
                   r.type_controle::TEXT AS type_eval,
                   r.type_controle::TEXT AS type_eval_label,
                   c.note AS note_actuelle, NULL::NUMERIC AS note_nouvelle,
                   pu.nom || ' ' || pu.prenom AS prof_nom,
                   r.message AS commentaire,
                   r.statut::TEXT AS statut,
                   r.date_creation AS date_soumission,
                   NULL AS raison_refus
            FROM reclamation r
            JOIN etudiant et  ON et.id = r.etudiant_id
            JOIN users u      ON u.id  = et.id
            JOIN enseignement e  ON e.id = r.enseignement_id
            JOIN professeur p    ON p.id = e.professeur_id
            JOIN users pu        ON pu.id = p.id
            LEFT JOIN controle c ON c.enseignement_id = e.id
                                AND c.type::TEXT = r.type_controle::TEXT
            WHERE r.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
    foreach ($_SESSION['reclamations'] as $r) {
        if ($r['id'] === $id) return $r;
    }
    return null;
}

function reclamation_ajouter(array $data): int {
    $pdo = get_pdo();
    if ($pdo) {
        $typeMap = ['ds' => 'DS', 'examen' => 'EXAM', 'tp' => 'TP'];
        $stmt = $pdo->prepare("
            INSERT INTO reclamation (message, type_controle, statut, enseignement_id, etudiant_id, admin_id)
            VALUES (:message, :type_controle, 'EN_ATTENTE', :enseignement_id, :etudiant_id, :admin_id)
            RETURNING id
        ");
        $stmt->execute([
            ':message'         => $data['commentaire'] ?? '',
            ':type_controle'   => $typeMap[$data['type_eval']] ?? 'DS',
            ':enseignement_id' => $data['enseignement_id'],
            ':etudiant_id'     => $_SESSION['user_id'] ?? 1,
            ':admin_id'        => 1,
        ]);
        return (int)$stmt->fetchColumn();
    }
    $id = $_SESSION['next_id']++;
    $data['id'] = $id;
    $_SESSION['reclamations'][] = $data;
    return $id;
}

function reclamation_update(int $id, array $changes): void {
    $pdo = get_pdo();
    if ($pdo) {
        $statutMap = [
            'en_attente'     => 'EN_ATTENTE',
            'approuve_admin' => 'ACCEPTEE',
            'refuse_admin'   => 'REFUSEE',
            'approuve_prof'  => 'ACCEPTEE',
            'refuse_prof'    => 'REFUSEE',
        ];
        if (isset($changes['statut'])) {
            $s = $statutMap[$changes['statut']] ?? strtoupper($changes['statut']);
            $pdo->prepare("UPDATE reclamation SET statut = ? WHERE id = ?")->execute([$s, $id]);
        }
        if (isset($changes['note_nouvelle'])) {
            $pdo->prepare("
                UPDATE controle SET note = ?
                WHERE enseignement_id = (SELECT enseignement_id FROM reclamation WHERE id = ?)
            ")->execute([$changes['note_nouvelle'], $id]);
        }
        return;
    }
    foreach ($_SESSION['reclamations'] as &$r) {
        if ($r['id'] === $id) {
            foreach ($changes as $k => $v) $r[$k] = $v;
            break;
        }
    }
    unset($r);
}

function _resolve_user_id(string $destinataire): ?int {
    $pdo = get_pdo();
    if ($destinataire === 'admin') {
        $row = $pdo->query("SELECT id FROM admin LIMIT 1")->fetch();
        return $row ? (int)$row['id'] : null;
    }
    if (str_starts_with($destinataire, 'etudiant_')) {
        return (int)substr($destinataire, strlen('etudiant_'));
    }
    return null;
}

function notification_ajouter(string $destinataire, string $message, int $ref_id): void {
    $pdo = get_pdo();
    if ($pdo) {
        $user_id = _resolve_user_id($destinataire);
        if ($user_id) {
            $pdo->prepare("INSERT INTO notification (message, user_id, createur_id) VALUES (?, ?, 1)")
                ->execute([$message, $user_id]);
        }
        return;
    }
    $_SESSION['notifications'][] = [
        'id'             => uniqid(),
        'destinataire'   => $destinataire,
        'message'        => $message,
        'reclamation_id' => $ref_id,
        'lu'             => false,
        'date'           => date('Y-m-d H:i'),
        'expiration'     => date('Y-m-d H:i', strtotime('+48 hours')),
    ];
}

function notifications_pour(string $destinataire): array {
    $pdo = get_pdo();
    if ($pdo) {
        $user_id = _resolve_user_id($destinataire);
        if (!$user_id) return [];
        $stmt = $pdo->prepare("
            SELECT id::TEXT, message, lue AS lu, date_creation AS date
            FROM notification WHERE user_id = ?
            ORDER BY date_creation DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    $now = date('Y-m-d H:i');
    $notifs = array_values(array_filter(
        $_SESSION['notifications'] ?? [],
        fn($n) => $n['destinataire'] === $destinataire && $n['expiration'] > $now
    ));
    usort($notifs, fn($a, $b) => strcmp($b['date'], $a['date']));
    return $notifs;
}

function notification_marquer_lue(string $id): void {
    $pdo = get_pdo();
    if ($pdo) {
        $pdo->prepare("UPDATE notification SET lue = TRUE WHERE id = ?")->execute([$id]);
        return;
    }
    foreach ($_SESSION['notifications'] as &$n) {
        if ($n['id'] === $id) { $n['lu'] = true; break; }
    }
    unset($n);
}
