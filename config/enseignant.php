<?php

$pdo = get_pdo();
$prof_id     = $_SESSION['user_id'] ?? null;
$profile_row = null;
$stats_db    = [];

if ($pdo && $prof_id) {
    $profile_row = $pdo->query("
        SELECT u.nom, u.prenom
        FROM users u JOIN professeur p ON p.id = u.id
        WHERE u.id = $prof_id
    ")->fetch(PDO::FETCH_ASSOC);

    $row = $pdo->query("
        SELECT COUNT(c.id) AS nb_controles,
               MAX(c.note) AS meilleure_note,
               ROUND(AVG(c.note)::NUMERIC, 2) AS moyenne
        FROM controle c
        JOIN enseignement e ON e.id = c.enseignement_id
        WHERE e.professeur_id = $prof_id
    ")->fetch(PDO::FETCH_ASSOC);
    $stats_db = $row ?: [];
}

return [
    'role' => 'Enseignant',
    'profile' => [
        'name' => $profile_row ? $profile_row['prenom'] . ' ' . $profile_row['nom'] : 'Aymen Sellaouti',
        'year' => '2025-2026',
    ],
    'nav' => [
        ['label' => 'Home',         'href' => '/?page=home'],
        ['label' => 'Blog',         'href' => '/?page=forum'],
        ['label' => 'Examens',      'href' => '/?page=qcm-create'],
        ['label' => 'Réclamations', 'href' => '/?page=prof-reclamations'],
    ],
    'stats' => [
        ['big' => true, 'value' => (string)($stats_db['nb_controles']    ?? '0'), 'label' => 'Contrôles saisis'],
        ['big' => true, 'value' => (string)($stats_db['meilleure_note']  ?? '—'), 'label' => 'Meilleure Note'],
        ['big' => true, 'value' => (string)($stats_db['moyenne']         ?? '—'), 'label' => 'Moyenne de la Classe'],
    ],
    'chart' => [
        'title'  => 'Distribution Évolutive des Notes',
        'legend' => [
            ['label' => 'DS',      'color' => 'blue'],
            ['label' => 'Examen',  'color' => 'red'],
            ['label' => 'Moyenne', 'color' => 'gray'],
        ],
    ],
    'actions' => [
        ['icon' => 'ti-clipboard-list', 'label' => 'Saisir Notes',  'href' => '/?page=qcm-scan'],
        ['icon' => 'ti-file-text',      'label' => 'Mes Examens',   'href' => '/?page=qcm-create'],
        ['icon' => 'ti-message-report', 'label' => 'Réclamations',  'href' => '/?page=prof-reclamations'],
    ],
];
