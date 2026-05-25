<?php
// config/etudiant.php — Configuration du rôle Étudiant

return [
    'role' => 'Étudiant',

    'profile' => [
        'name'  => 'Rayen Khammar',
        'class' => 'GL2-1',
        'year'  => '25-26',
    ],

    'nav' => [
        ['label' => 'Home',        'href' => 'index.php',        'active' => true],
        ['label' => 'Blog',        'href' => 'blog.php'],
        ['label' => 'Examens',     'href' => 'exams.php'],
        ['label' => 'Reclamation', 'href' => 'reclamation.php'],
    ],

    'stats' => [
        ['label' => 'Notes Acquises',  'value' => '10',        'icon' => 'ti-chart-bar'],
        ['sub'   => 'Dernière Réclam.','value' => 'Traitée'],
        ['sub'   => 'Dernière Note',   'value' => 'Java - 14'],
    ],

    'chart' => null,

    'actions' => [
        ['icon' => 'ti-news',          'label' => 'Voir Blog',          'href' => 'blog.php'],
        ['icon' => 'ti-chart-bar',     'label' => 'Voir moyenne',       'href' => 'moyenne.php'],
        ['icon' => 'ti-mail',          'label' => 'Nouvelle Réclamation','href' => 'reclamations.php'],
    ],
];