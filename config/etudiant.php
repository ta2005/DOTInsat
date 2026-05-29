<?php
$etuId      = (int)($_SESSION['user_id'] ?? 0);
$profil     = $etuId ? $etudiantRepo->getProfil($etuId) : null;
$classe     = $profil['classe'] ?? 'GL3-2';
$nomComplet = $profil
    ? trim($profil['prenom'] . ' ' . $profil['nom'])
    : 'Étudiant';
 
$nbNotes = $etuId ? $etudiantRepo->getNbNotes($etuId) : 0;
 
$derniereNoteRow = $etuId ? $etudiantRepo->getDerniereNote($etuId) : null;
$derniereNote    = $derniereNoteRow
    ? $derniereNoteRow['nom_matiere'] . ' — ' . number_format((float)$derniereNoteRow['note'], 2)
    : '—';
 
$reclamRow      = $etuId ? $etudiantRepo->getDerniereReclamation($etuId) : null;
$derniereReclam = $reclamRow
    ? ucfirst(strtolower(str_replace('_', ' ', $reclamRow['statut'])))
    : '—';

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
