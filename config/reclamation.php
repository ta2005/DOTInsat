<?php

$pdo = get_pdo();
$matieres   = [];
$types_eval = [
    ['value' => 'ds',     'label' => 'DS'],
    ['value' => 'examen', 'label' => 'Examen'],
    ['value' => 'tp',     'label' => 'TP'],
];

if ($pdo) {
    $rows = $pdo->query("
        SELECT e.id,
               e.nom,
               pu.nom || ' ' || pu.prenom AS prof,
               MAX(c.note) FILTER (WHERE c.type = 'DS')   AS ds,
               MAX(c.note) FILTER (WHERE c.type = 'EXAM') AS examen,
               MAX(c.note) FILTER (WHERE c.type = 'TP')   AS tp
        FROM enseignement e
        JOIN professeur p ON p.id = e.professeur_id
        JOIN users pu     ON pu.id = p.id
        LEFT JOIN controle c ON c.enseignement_id = e.id
        GROUP BY e.id, e.nom, pu.nom, pu.prenom
        ORDER BY e.nom
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $matieres[] = [
            'id'     => (string)$r['id'],
            'nom'    => $r['nom'],
            'prof'   => $r['prof'],
            'ds'     => $r['ds']     !== null ? (float)$r['ds']     : null,
            'examen' => $r['examen'] !== null ? (float)$r['examen'] : null,
            'tp'     => $r['tp']     !== null ? (float)$r['tp']     : null,
        ];
    }
} else {
    $matieres = [
        ['id' => 'java',    'nom' => 'Java',             'prof' => 'Dr. Ahmed Ben Ali',  'ds' => 14,   'examen' => 16,   'tp' => null],
        ['id' => 'algo',    'nom' => 'Algorithmique',    'prof' => 'Dr. Sonia Trabelsi', 'ds' => 12,   'examen' => 11,   'tp' => null],
        ['id' => 'bd',      'nom' => 'Bases de Données', 'prof' => 'Dr. Karim Meddeb',   'ds' => 17,   'examen' => 19.5, 'tp' => null],
        ['id' => 'reseaux', 'nom' => 'Réseaux',          'prof' => 'Dr. Leila Gharbi',   'ds' => 10,   'examen' => 13,   'tp' => null],
        ['id' => 'maths',   'nom' => 'Mathématiques',    'prof' => 'Dr. Nizar Hajji',    'ds' => 15,   'examen' => 14,   'tp' => null],
    ];
}

return [
    'nav' => [
        ['label' => 'Home',        'href' => '/?page=home'],
        ['label' => 'Blog',        'href' => '/?page=forum'],
        ['label' => 'Examens',     'href' => '/?page=examens'],
        ['label' => 'Réclamation', 'href' => '/?page=reclamation', 'active' => true],
        ['label' => 'Demande',     'href' => '/?page=demande'],
    ],
    'matieres'   => $matieres,
    'types_eval' => $types_eval,
];
