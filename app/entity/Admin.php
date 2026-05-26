<?php
declare(strict_types=1);

class Admin extends User
{
    public function __construct(
        ?int   $id,
        ?int   $cin,
        string $nom,
        string $prenom,
        string $email,
        string $motPasse,
        private string $titre = ''
    ) {
        parent::__construct($id, $cin, $nom, $prenom, $email, $motPasse);
    }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): void { $this->titre = $titre; }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'titre' => $this->titre,
        ]);
    }
}
