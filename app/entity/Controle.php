<?php
declare(strict_types=1);
namespace App\Entity;
class Controle
{
    public function __construct(
        private int $enseignement_id,
        private string $type,       // DS | EXAM | TP
        private string $statut,     // EN_ATTENTE | CORRIGE | VERIFIE | CONTESTE
        private string $format,     // QCM | MIX | NON_QCM
        private ?float $note = null,
        private ?int $id = null
    ) {
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getNote(): ?float
    {
        return $this->note;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function getStatut(): string
    {
        return $this->statut;
    }
    public function getFormat(): string
    {
        return $this->format;
    }
    public function getEnseignementId(): int
    {
        return $this->enseignement_id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function setNote(float $note): void
    {
        $this->note = $note;
    }

    public function __toString(): string
    {
        return sprintf(
            'Controle [ID: %s] | Type: %s | Format: %s | Statut: %s | Note: %s | Enseignement: %d',
            $this->id ?? 'NEW',
            $this->type,
            $this->format,
            $this->statut,
            $this->note ?? 'N/A',
            $this->enseignement_id
        );
    }
}
