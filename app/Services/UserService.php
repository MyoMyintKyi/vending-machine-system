<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\UserServiceInterface;
use App\Repositories\UserRepository;

final class UserService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {
    }

    public function countFiltered(array $filters = []): int
    {
        return $this->userRepository->countFiltered($filters);
    }

    public function getOverview(int $page = 1, int $perPage = 10, array $filters = [], string $sortBy = 'created_at', string $direction = 'desc'): array
    {
        $summary = $this->userRepository->summarizeFiltered($filters);

        return [
            'metrics' => [
                'total_users' => (int) ($summary['total_users'] ?? 0),
                'total_admins' => (int) ($summary['total_admins'] ?? 0),
                'total_standard_users' => (int) ($summary['total_standard_users'] ?? 0),
                'latest_joined_at' => (string) ($summary['latest_joined_at'] ?? '-'),
            ],
            'users' => $this->userRepository->listFiltered($filters, $page, $perPage, $sortBy, $direction),
        ];
    }
}