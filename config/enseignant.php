<?php
// config/enseignant.php — Configuration du rôle Enseignant

return [
    'role' => 'Enseignant',

    'profile' => [
        'name'    => 'Aymen Sellaouti',
        'year'    => '2025-2026',
        'classes' => ['GL2-1', 'GL2-2', 'RT2-1', 'MPI-1'],
    ],

    'nav' => [
        ['label' => 'Home',        'href' => 'index.php',        'active' => true],
        ['label' => 'Blog',        'href' => 'blog.php'],
        ['label' => 'Examens',     'href' => 'exams.php'],
        ['label' => 'Reclamation', 'href' => 'prof_reclamations.php'],
    ],

    // Type 'big' : valeur grande en haut, label en bas (style enseignant)
    'stats' => [
        ['big' => true, 'value' => '86',    'total' => '116', 'label' => 'Avancement des Notes'],
        ['big' => true, 'value' => '19,5',                    'label' => 'Meilleure Note'],
        ['big' => true, 'value' => '12,31',                   'label' => 'Moyenne de la Classe'],
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
        ['icon' => 'ti-clipboard-list', 'label' => 'Saisir Notes',  'href' => 'notes.php'],
        ['icon' => 'ti-file-text',      'label' => 'Mes Examens',   'href' => 'exams.php'],
        ['icon' => 'ti-message-report', 'label' => 'Réclamations',  'href' => 'prof_reclamations.php'],
    ],
];