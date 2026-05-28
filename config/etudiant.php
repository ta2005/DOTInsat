<?php
// config/etudiant.php
// Toutes les données sont injectées par HomeController::buildEtudiantConfig()
// Ce fichier ne contient aucune logique SQL — il assemble uniquement la config.

return [

    'role' => 'Étudiant',

    'profile' => [
        'name'  => $nomComplet ?? 'Étudiant',
        'class' => $classe     ?? 'GL3-2',
        'year'  => '2025-2026',
    ],

    'nav' => [
        ['label' => 'Home',         'href' => '/?page=home'],
        ['label' => 'Blog',         'href' => '/?page=forum'],
        ['label' => 'Examens',      'href' => '/?page=examens'],
        ['label' => 'Réclamations', 'href' => '/?page=reclamation'],
        ['label' => 'Demandes',     'href' => '/?page=demande'],
    ],

    'stats' => [
        ['label' => 'Notes Acquises',   'value' => (string)($nbNotes ?? 0), 'icon' => 'ti-chart-bar'],
        ['sub'   => 'Dernière Réclam.', 'value' => $derniereReclam ?? '—'],
        ['sub'   => 'Dernière Note',    'value' => $derniereNote   ?? '—'],
    ],

    'chart' => null,

    'actions' => [
        ['icon' => 'ti-news',      'label' => 'Voir Blog',            'href' => '/?page=forum'],
        ['icon' => 'ti-chart-bar', 'label' => 'Voir moyenne',         'href' => '/?page=examens'],
        ['icon' => 'ti-mail',      'label' => 'Nouvelle Réclamation', 'href' => '/?page=reclamation'],
        ['icon' => 'ti-mail',      'label' => 'Nouvelle Demande',     'href' => '/?page=demande'],
    ],
];
