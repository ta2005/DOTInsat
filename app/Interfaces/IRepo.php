<?php
// app/Interfaces/IRepo.php

declare(strict_types=1);

<<<<<<< HEAD
=======
namespace App\Interfaces;

>>>>>>> 56bcd12fcd57ae6195b0122a6ed19a38b8727d3c
interface IRepo
{
    public function fetchById(string $id): ?object;
    public function fetchAll(): array;
    public function delete(string $id): bool;
}