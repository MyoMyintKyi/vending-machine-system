<?php

declare(strict_types=1);

namespace App\Interfaces;

use Core\Request;

interface AuthServiceInterface
{
    public function attempt(string $identifier, string $password, Request $request): bool;

    public function logout(Request $request): void;

    public function user(Request $request): ?array;
}