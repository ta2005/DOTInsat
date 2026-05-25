<?php
declare(strict_types=1);

/**
 * Generic base repository interface.
 * All repositories must be able to fetch all records and fetch by ID.
 */
interface IRepo {
    public function fetchAll(): array;
    public function fetchById(int $id): mixed;
}
