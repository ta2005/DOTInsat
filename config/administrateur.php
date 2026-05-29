<?php
// config/administrateur.php
// $adminRepo est injecté par le controller avant le require

$stats       = $adminRepo->getStats();
$profile_row = $adminRepo->getProfil($_SESSION['user_id'] ?? 0);

return [
    'role' => 'Administrateur',
    'profile' => [
        'name'  => $profile_row ? $profile_row['prenom'] . ' ' . $profile_row['nom'] : 'Administrateur',
        // ken aandouch title nhotou administrateur
        'title' => $profile_row['titre'] ?? 'Administrateur',
        'year'  => '2025-2026',
    ],
    'nav' => [
        ['label' => 'Home',                'href' => '/?page=home'],
        ['label' => 'Blog',                'href' => '/?page=forum'],
        ['label' => 'Gestion Enseignants', 'href' => '/?page=ens_manage'],
        ['label' => 'Gestion Étudiants',   'href' => '/?page=etu_manage'],
        ['label' => 'Gestion Groupes',     'href' => '/?page=groupe_manage'],
        ['label' => 'Demandes',            'href' => '/?page=demandes'],
        ['label' => 'Réclamations',        'href' => '/?page=reclamations'],
    ],
    'stats' => [
        ['big' => true, 'value' => (string)($stats['nb_profs']         ?? 0),      'label' => 'Enseignants'],
        ['big' => true, 'value' => (string)($stats['demandes_attente'] ?? 0),       'label' => 'Demandes non traitées'],
        ['big' => true, 'value' => ($stats['taux_reussite']            ?? 0) . '%', 'label' => 'Taux de réussite global'],
    ],
   
    'actions' => [
        ['icon' => 'ti-users',          'label' => 'Gestion Enseignants', 'href' => '/?page=ens_manage'],
        ['icon' => 'ti-school',         'label' => 'Gestion Étudiants',   'href' => '/?page=etu_manage'],
        ['icon' => 'ti-users-group',    'label' => 'Gestion Groupes',     'href' => '/?page=groupe_manage'],
        ['icon' => 'ti-message-report', 'label' => 'Réclamations',        'href' => '/?page=reclamations'],
        ['icon' => 'ti-clipboard-list', 'label' => 'Demandes',            'href' => '/?page=demandes'],
        ['icon' => 'ti-news',           'label' => 'Blog',                'href' => '/?page=forum'],
    ],
];