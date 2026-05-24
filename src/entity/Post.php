<?php

class Post {
    public function __construct(
        private string $contenu,
        private int $id_user,
        private ?int $id_group = null,
        private ?DateTimeImmutable $date_de_creation = null,
        private ?int $id = null
    ) {
        // Default to current date/time if not provided
        $this->date_de_creation = $date_de_creation ?? new DateTimeImmutable();
    }

    /**
     * For Debugging
     */
    public function __toString(): string {
        return sprintf(
            "Post [%s] | Author ID: %d | Group ID: %s | Date: %s | Content: %s...",
            $this->id ?? 'NEW',
            $this->id_user,
            $this->id_group ?? 'Global',
            $this->date_de_creation->format('Y-m-d H:i'),
            substr(htmlspecialchars($this->contenu), 0, 30) // Show first 30 chars
        );
    }
}

?>
