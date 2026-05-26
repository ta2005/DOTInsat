<?php
declare(strict_types=1);

class Notification
{
    public function __construct(
        private string              $message,
        private int                 $userId,
        private int                 $createurId,
        private bool                $lue          = false,
        private ?DateTimeImmutable  $dateCreation = null,
        private ?int                $id           = null
    ) {
        $this->dateCreation = $dateCreation ?? new DateTimeImmutable();
    }

    public function getId(): ?int                      { return $this->id; }
    public function getMessage(): string               { return $this->message; }
    public function getUserId(): int                   { return $this->userId; }
    public function getCreateurId(): int               { return $this->createurId; }
    public function isLue(): bool                      { return $this->lue; }
    public function getDateCreation(): DateTimeImmutable { return $this->dateCreation; }

    public function setId(int $id): void    { $this->id = $id; }
    public function marquerLue(): void      { $this->lue = true; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'message'       => $this->message,
            'user_id'       => $this->userId,
            'createur_id'   => $this->createurId,
            'lue'           => $this->lue,
            'date_creation' => $this->dateCreation->format('Y-m-d H:i:s'),
        ];
    }
}
