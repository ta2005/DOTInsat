<?php
// app/Repositories/AdminRepository.php

require_once BASE_PATH . '/app/Repositories/Repository.php';

class AdminRepository extends Repository
{
    public function getStats(): array
    {
        if (!$this->isConnected()) return [];

        $demandes_attente     = (int)$this->db->query("SELECT COUNT(*) FROM demande WHERE statut = 'EN_ATTENTE'")->fetchColumn();
        $reclamations_attente = (int)$this->db->query("SELECT COUNT(*) FROM reclamation WHERE statut = 'EN_ATTENTE'")->fetchColumn();
        $nb_profs             = (int)$this->db->query("SELECT COUNT(*) FROM professeur")->fetchColumn();
        $nb_etudiants         = (int)$this->db->query("SELECT COUNT(*) FROM etudiant")->fetchColumn();
        //taux illi nejhou
        $taux = $this->db->query("
            SELECT ROUND(100.0 * COUNT(*) FILTER (WHERE note >= 10) / NULLIF(COUNT(*),0), 0)
            FROM controle
        ")->fetchColumn();

        return [
            'demandes_attente'     => $demandes_attente,
            'reclamations_attente' => $reclamations_attente,
            'nb_profs'             => $nb_profs,
            'nb_etudiants'         => $nb_etudiants,
            'taux_reussite'        => (int)($taux ?? 0),
        ];
    }

    public function getProfil(int $userId): ?array{
    if (!$this->isConnected()) return null;

    $stmt = $this->db->prepare("
        SELECT u.nom, u.prenom, a.titre
        FROM users u JOIN admin a ON a.id = u.id
        WHERE u.id = :id
    ");
    $stmt->execute([':id' => $userId]);
    // retouurner tableau associative
    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;}
}
