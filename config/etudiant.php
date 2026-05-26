<?php

$pdo = get_pdo();
$etu_id = $_SESSION['user_id'] ?? null;
$profile_row  = null;
$stats_db     = [];
$derniere_note   = '—';
$derniere_reclam = '—';

if ($pdo && $etu_id) {
    $profile_row = $pdo->query("
        SELECT u.nom, u.prenom,
               (et.niveau_scolaire_info).filiere AS filiere,
               (et.niveau_scolaire_info).annee   AS annee
        FROM users u JOIN etudiant et ON et.id = u.id
        WHERE u.id = $etu_id
    ")->fetch(PDO::FETCH_ASSOC);

    $row = $pdo->query("SELECT COUNT(id) AS nb_notes FROM controle")->fetch(PDO::FETCH_ASSOC);
    $stats_db = $row ?: [];

    $row = $pdo->query("
        SELECT e.nom AS matiere, c.note
        FROM controle c JOIN enseignement e ON e.id = c.enseignement_id
        ORDER BY c.id DESC LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
    if ($row) $derniere_note = $row['matiere'] . ' — ' . $row['note'];

    $stmt = $pdo->prepare("
        SELECT statut FROM reclamation
        WHERE etudiant_id = ? ORDER BY date_creation DESC LIMIT 1
    ");
    $stmt->execute([$etu_id]);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) $derniere_reclam = ucfirst(strtolower($r['statut']));
}

$classe = '';
if ($profile_row) {
    $classe = trim(($profile_row['filiere'] ?? '') . ' ' . ($profile_row['annee'] ?? ''));
}

return [
    'role' => 'Étudiant',
    'profile' => [
        'name'  => $profile_row ? $profile_row['prenom'] . ' ' . $profile_row['nom'] : 'Rayen Khammar',
        'class' => $classe ?: 'GL2-1',
        'year'  => '2025-2026',
    ],
    'nav' => [
        ['label' => 'Home',         'href' => '/?page=home'],
        ['label' => 'Blog',         'href' => '/?page=forum'],
        ['label' => 'Examens',      'href' => '/?page=examens'],
        ['label' => 'Réclamations', 'href' => '/?page=reclamation'],
        ['label' => 'Demandes',     'href' => '/?page=demande'],
    ],
    'stats' => [
        ['label' => 'Notes Acquises',   'value' => (string)($stats_db['nb_notes'] ?? '0'), 'icon' => 'ti-chart-bar'],
        ['sub'   => 'Dernière Réclam.', 'value' => $derniere_reclam],
        ['sub'   => 'Dernière Note',    'value' => $derniere_note],
    ],
    'chart'   => null,
    'actions' => [
        ['icon' => 'ti-news',      'label' => 'Voir Blog',            'href' => '/?page=forum'],
        ['icon' => 'ti-chart-bar', 'label' => 'Voir moyenne',         'href' => '/?page=examens'],
        ['icon' => 'ti-mail',      'label' => 'Nouvelle Réclamation', 'href' => '/?page=reclamation'],
        ['icon' => 'ti-mail',      'label' => 'Nouvelle Demande',     'href' => '/?page=demande'],
    ],
];
