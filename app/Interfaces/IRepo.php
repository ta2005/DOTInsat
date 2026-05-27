<?php
// app/Interfaces/IRepo.php

declare(strict_types=1);

interface IRepo
{
    public function fetchById(string $id): ?object;
    public function fetchAll(): array;
    public function delete(string $id): bool;
}