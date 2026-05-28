<?php
// config/enseignant.php
// $profRepo et $ensRepo sont injectés par ProfessorController avant le require

$prof_id = (int)($_SESSION['user_id'] ?? 0);

$profile_row     = $prof_id ? $profRepo->getProfil($prof_id) : null;
$selection       = $prof_id ? $profRepo->getEnseignementsAvecSelection($prof_id, $_GET['selected_class'] ?? null) : [];
$classes_rows    = $selection['classes']        ?? [];
$enseignement_id = $selection['enseignement_id'] ?? null;
$selected_nom    = $selection['selected_nom']    ?? '—';

$stats_db = ($prof_id && $enseignement_id !== null)
    ? $ensRepo->getStatsByProfesseur($prof_id, $enseignement_id)
    : [];

return [
    'role'    => 'Enseignant',
    'profile' => [
        'name'           => $profile_row
            ? $profile_row['prenom'] . ' ' . $profile_row['nom']
            : '—',
        'year'           => '2025-2026',
        'classes'        => $classes_rows,
        'selected_class' => $selected_nom,
    ],
    'nav' => [
        ['label' => 'Home',         'href' => '/?page=home'],
        ['label' => 'Blog',         'href' => '/?page=forum'],
        ['label' => 'Examens',      'href' => '/?page=examens-prof'],
        ['label' => 'Réclamations', 'href' => '/?page=prof-reclamations'],
    ],
    'stats' => [
        ['big' => true, 'value' => (string)($stats_db['nb_controles']   ?? '0'), 'label' => 'Contrôles saisis'],
        ['big' => true, 'value' => (string)($stats_db['meilleure_note'] ?? '—'), 'label' => 'Meilleure Note'],
        ['big' => true, 'value' => (string)($stats_db['moyenne']        ?? '—'), 'label' => 'Moyenne de la Classe'],
    ],
    'actions' => [
        ['icon' => 'ti-layout-dashboard', 'label' => 'Dashboard Examens', 'href' => '/?page=examens-prof'],
        ['icon' => 'ti-message-report',   'label' => 'Réclamations',      'href' => '/?page=prof-reclamations'],
    ],
];
