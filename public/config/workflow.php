<?php
// config/workflow.php — Données initiales du workflow réclamation
//
// Statuts possibles d'une réclamation :
//   en_attente      → soumise par l'étudiant, pas encore traitée par l'admin
//   approuve_admin  → approuvée par l'admin, transmise au professeur
//   refuse_admin    → refusée par l'admin
//   approuve_prof   → acceptée par le professeur (note modifiée)
//   refuse_prof     → refusée par le professeur

return [

    'reclamations' => [
        [
            'id'              => 1,
            'etudiant_id'     => 'etudiant_1',
            'nom'             => 'Khammar',
            'prenom'          => 'Rayen',
            'num'             => '2024GL1234',
            'matiere_id'      => 'algo',
            'matiere_nom'     => 'Algorithmique',
            'type_eval'       => 'examen',
            'type_eval_label' => 'Examen',
            'note_actuelle'   => 11,
            'prof_nom'        => 'Dr. Sonia Trabelsi',
            'commentaire'     => "Je pense que ma copie n'a pas été entièrement corrigée.",
            'statut'          => 'en_attente',
            'date_soumission' => '2026-05-20 14:32',
            'raison_refus'    => null,
            'note_nouvelle'   => null,
        ],
        [
            'id'              => 2,
            'etudiant_id'     => 'etudiant_1',
            'nom'             => 'Khammar',
            'prenom'          => 'Rayen',
            'num'             => '2024GL1234',
            'matiere_id'      => 'java',
            'matiere_nom'     => 'Java',
            'type_eval'       => 'ds',
            'type_eval_label' => 'DS',
            'note_actuelle'   => 14,
            'prof_nom'        => 'Dr. Ahmed Ben Ali',
            'commentaire'     => "Erreur de correction sur la question 3.",
            'statut'          => 'approuve_admin',
            'date_soumission' => '2026-05-18 09:15',
            'raison_refus'    => null,
            'note_nouvelle'   => null,
        ],
    ],

    // Les notifications sont vides au démarrage.
    // Elles s'accumulent au fil des actions du workflow et expirent après 48 h.
    'notifications' => [],
];
