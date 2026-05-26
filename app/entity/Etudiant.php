<?php
declare(strict_types=1);

class Etudiant extends User
{
    public function __construct(
        ?int                   $id,
        ?int                   $cin,
        string                 $nom,
        string                 $prenom,
        string                 $email,
        string                 $motPasse,
        private ?NiveauScolaire $niveauScolaire = null
    ) {
        parent::__construct($id, $cin, $nom, $prenom, $email, $motPasse);
    }

    public function getNiveauScolaire(): ?NiveauScolaire { return $this->niveauScolaire; }
    public function setNiveauScolaire(NiveauScolaire $n): void { $this->niveauScolaire = $n; }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'niveau_scolaire' => $this->niveauScolaire?->toArray(),
        ]);
    }
}
