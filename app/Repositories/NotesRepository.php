<?php
// repositories/NotesRepository.php


class NotesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    
    public function getNotesBySemestre(
        int    $etudiantId,
        string $filiere,
        int    $niveau,
        int    $semestre
    ): array {

        $req = $this->pdo->prepare("
            SELECT
                m.id              AS matiere_id,
                m.nom_matiere,
                m.coefficient,
                m.types_controle,
                c.type            AS type_controle,
                c.note,
                c.statut
            FROM matieres m
            LEFT JOIN enseignement e
                ON e.matiere_id = m.id
            LEFT JOIN controle c
                ON c.enseignement_id = e.id
                AND c.etudiant_id = :etudiant_id
            WHERE
                m.filiere  = :filiere
                AND m.niveau   = :niveau
                AND m.semestre = :semestre
            ORDER BY
                m.nom_matiere ASC,
                c.type ASC
        ");

        $req->execute([
            ':etudiant_id' => $etudiantId,
            ':filiere'     => $filiere,
            ':niveau'      => $niveau,
            ':semestre'    => $semestre,
        ]);

        $rows = $req->fetchAll(PDO::FETCH_ASSOC);

    
        $matieres = [];

        foreach ($rows as $row) {

            $mid = $row['matiere_id'];

            if (!isset($matieres[$mid])) {

                $matieres[$mid] = [
                    'nom_matiere'    => $row['nom_matiere'],
                    'coefficient'    => (float) $row['coefficient'],
                    'types_controle' => $this->parseTypesControle($row['types_controle']),
                    'notes'          => [],
                ];
            }

            if ($row['note'] !== null) {

                $matieres[$mid]['notes'][$row['type_controle']] = [
                    'note'   => (float) $row['note'],
                    'statut' => $row['statut'],
                ];
            }
        }

        return $matieres;
    }

    
    private function parseTypesControle(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }

        return explode(',', trim($raw, '{}'));
    }
}
