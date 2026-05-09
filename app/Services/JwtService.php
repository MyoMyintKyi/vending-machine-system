<?php

declare(strict_types=1);

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;

final class JwtService
{
    private string $secret;

    private int $ttl;

    public function __construct(?string $secret = null, ?int $ttl = null)
    {
        $this->secret = $secret ?? (string) env('JWT_SECRET', 'change-me-in-env');
        $this->ttl = $ttl ?? max(60, (int) env('JWT_TTL', '3600'));
    }

    public function issueToken(array $user): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->ttl;

        $payload = [
            'sub' => (int) ($user['id'] ?? 0),
            'username' => (string) ($user['username'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'role' => (string) ($user['role'] ?? 'User'),
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function decodeToken(string $token): array
    {
        if (trim($token) === '') {
            throw new RuntimeException('Missing bearer token.');
        }

        $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));

        return (array) $decoded;
    }

    public function ttl(): int
    {
        return $this->ttl;
    }
}