<?php

$pdo = get_pdo();
$stats    = [];
$profile_row = null;

if ($pdo) {
    $stats['demandes_attente']     = (int)$pdo->query("SELECT COUNT(*) FROM demande WHERE statut = 'EN_ATTENTE'")->fetchColumn();
    $stats['reclamations_attente'] = (int)$pdo->query("SELECT COUNT(*) FROM reclamation WHERE statut = 'EN_ATTENTE'")->fetchColumn();
    $stats['nb_profs']             = (int)$pdo->query("SELECT COUNT(*) FROM professeur")->fetchColumn();
    $stats['nb_etudiants']         = (int)$pdo->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
    $taux = $pdo->query("
        SELECT ROUND(100.0 * COUNT(*) FILTER (WHERE note >= 10) / NULLIF(COUNT(*),0), 0)
        FROM controle
    ")->fetchColumn();
    $stats['taux_reussite'] = (int)($taux ?? 0);

    $profile_row = $pdo->query("
        SELECT u.nom, u.prenom, a.titre
        FROM users u JOIN admin a ON a.id = u.id LIMIT 1
    ")->fetch(PDO::FETCH_ASSOC);
}

return [
    'role' => 'Administrateur',
    'profile' => [
        'name'  => $profile_row ? $profile_row['prenom'] . ' ' . $profile_row['nom'] : 'Mohamed Ali Ben Salah',
        'title' => $profile_row['titre'] ?? 'Administrateur',
        'year'  => '2025-2026',
    ],
    'nav' => [
        ['label' => 'Home',               'href' => '/?page=home',              'active' => true],
        ['label' => 'Blog',               'href' => '/?page=forum'],
        ['label' => 'Gestion Enseignants','href' => '/?page=ens_manage'],
        ['label' => 'Gestion Étudiants',  'href' => '/?page=etu_manage'],
        ['label' => 'Demandes',           'href' => '/?page=demandes'],
        ['label' => 'Réclamations',       'href' => '/?page=reclamations'],
    ],
    'stats' => [
        ['big' => true, 'value' => (string)($stats['nb_profs'] ?? 0),        'label' => 'Enseignants'],
        ['big' => true, 'value' => (string)($stats['demandes_attente'] ?? 0), 'label' => 'Demandes non traitées'],
        ['big' => true, 'value' => ($stats['taux_reussite'] ?? 0) . '%',      'label' => 'Taux de réussite global'],
    ],
    'chart'   => null,
    'actions' => [
        ['icon' => 'ti-users',          'label' => 'Gestion Enseignants', 'href' => '/?page=ens_manage'],
        ['icon' => 'ti-school',         'label' => 'Gestion Étudiants',   'href' => '/?page=etu_manage'],
        ['icon' => 'ti-clipboard-list', 'label' => 'Demandes',            'href' => '/?page=admin-demandes'],
        ['icon' => 'ti-news',           'label' => 'Blog',                'href' => '/?page=forum'],
        ['icon' => 'ti-chart-bar',      'label' => 'Statistiques',        'href' => '/?page=stats'],
        ['icon' => 'ti-settings',       'label' => 'Paramètres',          'href' => '/?page=settings'],
    ],
];
