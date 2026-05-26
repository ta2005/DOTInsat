<?php
// repositories/MatiereRepository.php


class MatiereRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /*
    |--------------------------------------------------------------------------
    | Récupérer les matières
    |--------------------------------------------------------------------------
    */
    public function getMatieres($filiere, $niveau, $semestre)
    {
        $req = $this->pdo->prepare("
            SELECT
                id,
                nom_matiere,
                coefficient,
                types_controle
            FROM matieres
            WHERE filiere = :filiere
            AND niveau = :niveau
            AND semestre = :semestre
            ORDER BY id
        ");

        $req->execute([
            ':filiere' => $filiere,
            ':niveau' => $niveau,
            ':semestre' => $semestre
        ]);

        $matieres = $req->fetchAll(PDO::FETCH_ASSOC);

        /*
        |--------------------------------------------------------------------------
        | Convertir tableau PostgreSQL → tableau PHP
        |--------------------------------------------------------------------------
        */
        foreach ($matieres as &$matiere) {

            if (!empty($matiere['types_controle'])) {

                $matiere['types_controle'] = explode(
                    ',',
                    trim($matiere['types_controle'], '{}')
                );

            } else {

                $matiere['types_controle'] = [];
            }
        }

        return $matieres;
    }

    /*
    |--------------------------------------------------------------------------
    | Vérifier DS
    |--------------------------------------------------------------------------
    */
    public function hasDS(array $matiere): bool
    {
        return in_array('DS', $matiere['types_controle']);
    }

    /*
    |--------------------------------------------------------------------------
    | Vérifier EXAM
    |--------------------------------------------------------------------------
    */
    public function hasExam(array $matiere): bool
    {
        return in_array('EXAM', $matiere['types_controle']);
    }

    /*
    |--------------------------------------------------------------------------
    | Vérifier TP
    |--------------------------------------------------------------------------
    */
    public function hasTP(array $matiere): bool
    {
        return in_array('TP', $matiere['types_controle']);
    }
}