<?php
declare(strict_types=1);

class Commentaire
{
    public function __construct(
        private string              $contenu,
        private int                 $postId,
        private int                 $auteurId,
        private ?DateTimeImmutable  $dateCreation = null,
        private ?int                $id           = null
    ) {
        $this->dateCreation = $dateCreation ?? new DateTimeImmutable();
    }

    public function getId(): ?int                      { return $this->id; }
    public function getContenu(): string               { return $this->contenu; }
    public function getPostId(): int                   { return $this->postId; }
    public function getAuteurId(): int                 { return $this->auteurId; }
    public function getDateCreation(): DateTimeImmutable { return $this->dateCreation; }

    public function setId(int $id): void { $this->id = $id; }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'contenu'       => $this->contenu,
            'post_id'       => $this->postId,
            'auteur_id'     => $this->auteurId,
            'date_creation' => $this->dateCreation->format('Y-m-d H:i:s'),
        ];
    }
}
