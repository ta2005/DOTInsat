<?php
declare(strict_types=1);

class Reclamation
{
    

    public function __construct(
        private string              $message,
        private string              $typeControle,
        private string              $statut,
        private int                 $enseignementId,
        private int                 $etudiantId,
        private ?int                $adminId       = null,
        private ?DateTimeImmutable  $dateCreation  = null,
        private ?int                $id            = null
    ) {
        $this->dateCreation = $dateCreation ?? new DateTimeImmutable();
    }

    public function getId(): ?int                      { return $this->id; }
    public function getMessage(): string               { return $this->message; }
    public function getTypeControle(): string          { return $this->typeControle; }
    public function getStatut(): string                { return $this->statut; }
    public function getEnseignementId(): int           { return $this->enseignementId; }
    public function getEtudiantId(): int               { return $this->etudiantId; }
    public function getAdminId(): ?int                 { return $this->adminId; }
    public function getDateCreation(): DateTimeImmutable { return $this->dateCreation; }

    public function setId(int $id): void       { $this->id = $id; }
    public function setStatut(string $s): void { $this->statut = $s; }
    public function setAdminId(int $id): void  { $this->adminId = $id; }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'message'         => $this->message,
            'type_controle'   => $this->typeControle,
            'statut'          => $this->statut,
            'enseignement_id' => $this->enseignementId,
            'etudiant_id'     => $this->etudiantId,
            'admin_id'        => $this->adminId,
            'date_creation'   => $this->dateCreation->format('Y-m-d H:i:s'),
        ];
    }
}
