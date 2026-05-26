<?php
declare(strict_types=1);

class Enseignement
{
    public function __construct(
        private string              $nom,
        private ?DateTimeImmutable  $dateDebut,
        private ?NiveauScolaire     $niveauScolaire = null,
        private ?int                $professeurId   = null,
        private ?int                $id             = null
    ) {}

    public function getId(): ?int                      { return $this->id; }
    public function getNom(): string                   { return $this->nom; }
    public function getDateDebut(): ?DateTimeImmutable { return $this->dateDebut; }
    public function getNiveauScolaire(): ?NiveauScolaire { return $this->niveauScolaire; }
    public function getProfesseurId(): ?int            { return $this->professeurId; }

    public function setId(int $id): void { $this->id = $id; }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'nom'             => $this->nom,
            'date_debut'      => $this->dateDebut?->format('Y-m-d H:i:s'),
            'niveau_scolaire' => $this->niveauScolaire?->toArray(),
            'professeur_id'   => $this->professeurId,
        ];
    }
}
