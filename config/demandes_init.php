<?php
// config/demandes_init.php — Données initiales des demandes administratives
//
// Statuts possibles d'une demande :
//   en_attente  → soumise par l'étudiant, pas encore traitée
//   approuve    → approuvée par l'administrateur
//   refuse      → refusée par l'administrateur

return [
    [
        'id'              => 1,
        'nom'             => 'Khammar',
        'prenom'          => 'Rayen',
        'num'             => '2024GL1234',
        'type'            => 'attestation',
        'type_label'      => "Demande d'attestation d'inscription",
        'commentaire'     => "J'ai besoin de cette attestation pour ma demande de visa.",
        'statut'          => 'en_attente',
        'date_soumission' => '2026-05-21 10:15',
        'raison_refus'    => null,
        'reponse_admin'   => null,
    ],
    [
        'id'              => 2,
        'nom'             => 'Mansouri',
        'prenom'          => 'Sarra',
        'num'             => '2024RT5678',
        'type'            => 'stage',
        'type_label'      => "Demande de stage d'été",
        'commentaire'     => "Demande de convention de stage pour la société XYZ du 01/07 au 31/08.",
        'statut'          => 'en_attente',
        'date_soumission' => '2026-05-22 14:30',
        'raison_refus'    => null,
        'reponse_admin'   => null,
    ],
    [
        'id'              => 3,
        'nom'             => 'Boukadi',
        'prenom'          => 'Ahmed',
        'num'             => '2023GL9901',
        'type'            => 'salle',
        'type_label'      => 'Demande de réservation de salle',
        'commentaire'     => "Réservation de la salle A201 le 28/05 de 14h à 17h pour une session de travail de groupe.",
        'statut'          => 'approuve',
        'date_soumission' => '2026-05-19 09:00',
        'raison_refus'    => null,
        'reponse_admin'   => 'Salle réservée. Venez récupérer la clé à l\'accueil.',
    ],
];
