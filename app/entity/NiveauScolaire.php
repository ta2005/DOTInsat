<?php
declare(strict_types=1);


class NiveauScolaire
{
    public function __construct(
        private string $classe,
        private int    $annee,
        private string $niveau,
        private string $filiere
    ) {}

    public function getClasse(): string  { return $this->classe; }
    public function getAnnee(): int      { return $this->annee; }
    public function getNiveau(): string  { return $this->niveau; }
    public function getFiliere(): string { return $this->filiere; }

    public function toArray(): array
    {
        return [
            'classe'  => $this->classe,
            'annee'   => $this->annee,
            'niveau'  => $this->niveau,
            'filiere' => $this->filiere,
        ];
    }

    public function __toString(): string
    {
        return "{$this->classe} | {$this->filiere} | {$this->niveau} ({$this->annee})";
    }
}
