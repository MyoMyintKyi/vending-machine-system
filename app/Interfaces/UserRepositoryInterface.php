<?php

declare(strict_types=1);

namespace App\Interfaces;

interface UserRepositoryInterface
{
    public function create(array $data): int;

    public function findById(int $id): ?array;

    public function findByEmail(string $email): ?array;

    public function findByUsername(string $username): ?array;
}