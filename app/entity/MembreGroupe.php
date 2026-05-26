<?php
declare(strict_types=1);


class MembreGroupe
{
    public function __construct(
        private int                 $userId,
        private int                 $groupeId,
        private ?DateTimeImmutable  $dateAdhesion = null,
        private ?int                $id           = null
    ) {
        $this->dateAdhesion = $dateAdhesion ?? new DateTimeImmutable();
    }

    public function getId(): ?int                      { return $this->id; }
    public function getUserId(): int                   { return $this->userId; }
    public function getGroupeId(): int                 { return $this->groupeId; }
    public function getDateAdhesion(): DateTimeImmutable { return $this->dateAdhesion; }

    public function setId(int $id): void { $this->id = $id; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->userId,
            'groupe_id'     => $this->groupeId,
            'date_adhesion' => $this->dateAdhesion->format('Y-m-d H:i:s'),
        ];
    }
}
