<?php
declare(strict_types=1);

class Professeur extends User
{
    public function __construct(
        ?int   $id,
        ?int   $cin,
        string $nom,
        string $prenom,
        string $email,
        string $motPasse
    ) {
        parent::__construct($id, $cin, $nom, $prenom, $email, $motPasse);
    }

    public function toArray(): array
    {
        return parent::toArray();
    }
}
