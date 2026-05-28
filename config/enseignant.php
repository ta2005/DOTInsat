<?php
// config/enseignant.php
// $pdo est injecté par ProfessorController (via $pdo = $this->pdo avant le require)

$prof_id = $_SESSION['user_id'] ?? null;

$profile_row    = null;
$stats_db       = [];
$classes_rows   = [];
$enseignement_id = null;

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

    // Récupérer tous les enseignements du prof (id + nom)
    $all_ens_stmt = $pdo->prepare("
        SELECT id, nom
        FROM enseignement
        WHERE professeur_id = :prof_id
        ORDER BY nom
    ");
    $all_ens_stmt->execute([':prof_id' => $prof_id]);
    $all_enseignements = $all_ens_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Noms pour le dropdown
    $classes_rows = array_column($all_enseignements, 'nom');

    // Classe sélectionnée (GET ou première par défaut)
    $selected_nom = $_GET['selected_class'] ?? ($classes_rows[0] ?? null);

    // Trouver l'id de l'enseignement sélectionné
    foreach ($all_enseignements as $ens) {
        if ($ens['nom'] === $selected_nom) {
            $enseignement_id = (int)$ens['id'];
            break;
        }
    }

    // Stats filtrées sur l'enseignement sélectionné
    $stats_db = $ensRepo->getStatsByProfesseur((int)$prof_id, $enseignement_id);
}

return [
    'role'    => 'Enseignant',
    'profile' => [
        'name'            => $profile_row
            ? $profile_row['prenom'] . ' ' . $profile_row['nom']
            : '—',
        'year'            => '2025-2026',
        'classes'         => $classes_rows,
        'selected_class'  => $_GET['selected_class'] ?? ($classes_rows[0] ?? '—'),
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
    // chart supprimé
    'actions' => [
        ['icon' => 'ti-layout-dashboard', 'label' => 'Dashboard Examens', 'href' => '/?page=examens-prof'],
        ['icon' => 'ti-message-report',   'label' => 'Réclamations',      'href' => '/?page=prof-reclamations'],
    ],
];
