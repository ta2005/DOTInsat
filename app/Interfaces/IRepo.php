<?php
declare(strict_types=1);

namespace App\Interfaces;

interface IRepo
{
    public function fetchById(string $id): ?object;
    public function fetchAll(): array;
    public function delete(string $id): bool;
}