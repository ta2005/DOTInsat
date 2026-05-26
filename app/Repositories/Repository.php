<?php

abstract class Repository
{
    protected ?PDO $db;

    public function __construct(?PDO $db)
    {
        $this->db = $db;
    }

    protected function isConnected(): bool
    {
        return $this->db !== null;
    }
}