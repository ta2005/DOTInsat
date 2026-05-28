<?php
// config/enseignant.php
// $pdo est injecté par ProfessorController (via $pdo = $this->pdo avant le require)

$prof_id = $_SESSION['user_id'] ?? null;

$profile_row  = null;
$stats_db     = [];
$classes_rows = [];

if ($pdo && $prof_id) {
    require_once BASE_PATH . '/app/Repositories/EnseignementRepository.php';
    $ensRepo = new EnseignementRepository($pdo);

    $profile_stmt = $pdo->prepare("
        SELECT u.nom, u.prenom
        FROM users u
        JOIN professeur p ON p.id = u.id
        WHERE u.id = :id
    ");
    $profile_stmt->execute([':id' => $prof_id]);
    $profile_row = $profile_stmt->fetch(PDO::FETCH_ASSOC);

    $classes_rows = $ensRepo->getNomsByProfesseur((int)$prof_id);
    $stats_db     = $ensRepo->getStatsByProfesseur((int)$prof_id);
}

return [
    'role'    => 'Enseignant',
    'profile' => [
        'name'    => $profile_row
            ? $profile_row['prenom'] . ' ' . $profile_row['nom']
            : '—',
        'year'    => '2025-2026',
        'classes' => $classes_rows,
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
    'chart' => [
        'title'  => 'Distribution Évolutive des Notes',
        'legend' => [
            ['label' => 'DS',      'color' => 'blue'],
            ['label' => 'Examen',  'color' => 'red'],
            ['label' => 'Moyenne', 'color' => 'gray'],
        ],
    ],
    'actions' => [
        ['icon' => 'ti-layout-dashboard', 'label' => 'Dashboard Examens', 'href' => '/?page=examens-prof'],
        ['icon' => 'ti-message-report',   'label' => 'Réclamations',      'href' => '/?page=prof-reclamations'],
    ],
];
