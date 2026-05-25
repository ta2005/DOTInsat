<?php
// config/reclamation.php — Configuration de la page réclamation étudiant

return [

    'nav' => [
        ['label' => 'Home',        'href' => 'index.php'],
        ['label' => 'Blog',        'href' => 'blog.php'],
        ['label' => 'Examens',     'href' => 'exams.php'],
        ['label' => 'Reclamation', 'href' => 'reclamation.php', 'active' => true],
        ['label' => 'Demande',     'href' => 'demande.php'],
    ],

    // Chaque matière contient : id, nom, enseignant responsable, note DS, note Examen
    'matieres' => [
        ['id' => 'java',    'nom' => 'Java',              'prof' => 'Dr. Ahmed Ben Ali',  'ds' => 14,   'examen' => 16  ],
        ['id' => 'algo',    'nom' => 'Algorithmique',     'prof' => 'Dr. Sonia Trabelsi', 'ds' => 12,   'examen' => 11  ],
        ['id' => 'bd',      'nom' => 'Bases de Données',  'prof' => 'Dr. Karim Meddeb',   'ds' => 17,   'examen' => 19.5],
        ['id' => 'reseaux', 'nom' => 'Réseaux',           'prof' => 'Dr. Leila Gharbi',   'ds' => 10,   'examen' => 13  ],
        ['id' => 'maths',   'nom' => 'Mathématiques',     'prof' => 'Dr. Nizar Hajji',    'ds' => 15,   'examen' => 14  ],
    ],

    // Types d'évaluation disponibles pour une réclamation
    'types_eval' => [
        ['value' => 'ds',     'label' => 'DS'],
        ['value' => 'examen', 'label' => 'Examen'],
        ['value' => 'tp',     'label' => 'TP'],
    ],
];
