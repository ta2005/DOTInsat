<?php
// config/admin.php — Configuration du rôle Administrateur

return [
    'role' => 'Administrateur',

    'profile' => [
        'name'  => 'Mohamed Ali Ben Salah',
        'title' => 'Administrateur',
        'year'  => '2025-2026',
    ],

    'nav' => [
        ['label' => 'Home',               'href' => 'index.php',          'active' => true],
        ['label' => 'Blog',               'href' => 'blog.php'],
        ['label' => 'Gestion Enseignants','href' => 'ens_manage.php'],
        ['label' => 'Gestion Étudiants',  'href' => 'etu_manage.php'],
        ['label' => 'Demandes',           'href' => 'admin_demandes.php'],
        ['label' => 'Réclamations', 'href' => 'admin_reclamations.php'],
    ],

    'stats' => [
        [
            'big'   => true,
            'value' => '74',
            'total' => '116',
            'label' => 'Enseignants ayant rendu leurs notes',
        ],
        [
            'big'   => true,
            'value' => '12',
            'label' => 'Demandes non traitées',
        ],
        [
            'big'   => true,
            'value' => '78%',
            'label' => 'Taux de réussite global',
        ],
    ],

    'chart' => null,

    'actions' => [
        ['icon' => 'ti-users',          'label' => 'Gestion Enseignants', 'href' => 'ens_manage.php'],
        ['icon' => 'ti-school',         'label' => 'Gestion Étudiants',   'href' => 'etu_manage.php'],
        ['icon' => 'ti-clipboard-list', 'label' => 'Demandes',            'href' => 'admin_demandes.php'],
        ['icon' => 'ti-news',           'label' => 'Blog',                'href' => 'blog.php'],
        ['icon' => 'ti-chart-bar',      'label' => 'Statistiques',        'href' => 'stats.php'],
        ['icon' => 'ti-settings',       'label' => 'Paramètres',          'href' => 'settings.php'],
    ],
];