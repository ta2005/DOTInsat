<?php
// public/config/etudiant.php

return [
    'role' => 'Étudiant',
    'nav' => [
        ['label' => 'Accueil', 'href' => 'index.php', 'active' => true],
        ['label' => 'Groupes', 'href' => 'groups.php'],
        ['label' => 'Communauté', 'href' => 'feed.php']
    ],
    'profile' => [
        'name' => 'Aymen (Membre)',
        'class' => 'Génie Logiciel',
        'year' => 'Membre depuis 2024'
    ],
    // These will be picked up by your actions.php component
    'stats' => [
        ['label' => 'Mes Posts', 'value' => '12', 'icon' => 'ti-article'],
        ['label' => 'Groupes Rejoints', 'value' => '3', 'icon' => 'ti-users']
    ],
    'actions' => [
        ['label' => 'Nouveau Post', 'icon' => 'ti-pencil', 'href' => 'create_post.php'],
        ['label' => 'Explorer les Groupes', 'icon' => 'ti-search', 'href' => 'groups.php']
    ]
];
