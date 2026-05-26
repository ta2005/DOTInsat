<?php
declare(strict_types=1);

class Demande
{


    public function __construct(
        private string              $message,
        private string              $type,
        private string              $statut,
        private int                 $userId,
        private ?int                $adminId      = null,
        private ?DateTimeImmutable  $dateCreation = null,
        private ?int                $id           = null
    ) {
        $this->dateCreation = $dateCreation ?? new DateTimeImmutable();
    }

    public function getId(): ?int                      { return $this->id; }
    public function getMessage(): string               { return $this->message; }
    public function getType(): string                  { return $this->type; }
    public function getStatut(): string                { return $this->statut; }
    public function getUserId(): int                   { return $this->userId; }
    public function getAdminId(): ?int                 { return $this->adminId; }
    public function getDateCreation(): DateTimeImmutable { return $this->dateCreation; }

    public function setId(int $id): void       { $this->id = $id; }
    public function setStatut(string $s): void { $this->statut = $s; }
    public function setAdminId(int $id): void  { $this->adminId = $id; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'message'       => $this->message,
            'type'          => $this->type,
            'statut'        => $this->statut,
            'user_id'       => $this->userId,
            'admin_id'      => $this->adminId,
            'date_creation' => $this->dateCreation->format('Y-m-d H:i:s'),
        ];
    }
}
