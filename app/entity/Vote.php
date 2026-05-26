<?php
declare(strict_types=1);

class Vote
{
    public function __construct(
        private string $type,    // UPVOTE | DOWNVOTE
        private int    $postId,
        private int    $userId,
        private ?int   $id = null
    ) {}

    public function getId(): ?int    { return $this->id; }
    public function getType(): string { return $this->type; }
    public function getPostId(): int  { return $this->postId; }
    public function getUserId(): int  { return $this->userId; }

    public function setId(int $id): void { $this->id = $id; }

    public function toArray(): array
    {
        return [
            'id'      => $this->id,
            'type'    => $this->type,
            'post_id' => $this->postId,
            'user_id' => $this->userId,
        ];
    }
}
