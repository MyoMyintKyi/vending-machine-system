<?php

declare(strict_types=1);

namespace App\Interfaces;

interface ProductServiceInterface
{
    public function findAll(int $page = 1, int $perPage = 10, string $sortBy = 'name', string $direction = 'asc'): array;

    public function countAll(): int;

    public function findById(int $id): ?array;

    public function create(array $data): int;

    public function update(int $id, array $data): bool;

    public function delete(int $id): bool;
}