<?php
// config/demande.php — Configuration de la page demande étudiant

return [

    'nav' => [
        ['label' => 'Home',        'href' => 'index.php'],
        ['label' => 'Blog',        'href' => 'blog.php'],
        ['label' => 'Examens',     'href' => 'exams.php'],
        ['label' => 'Reclamation', 'href' => 'reclamation.php'],
        ['label' => 'Demande',     'href' => 'demande.php', 'active' => true],
    ],

    // Types de demandes administratives disponibles
    'types_demande' => [
        ['value' => 'stage',       'label' => "Demande de stage d'été"],
        ['value' => 'attestation', 'label' => "Demande d'attestation d'inscription"],
        ['value' => 'salle',       'label' => 'Demande de réservation de salle'],
        ['value' => 'autre',       'label' => 'Autre'],
    ],
];
