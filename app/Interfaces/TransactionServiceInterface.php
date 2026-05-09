<?php

declare(strict_types=1);

namespace App\Interfaces;

interface TransactionServiceInterface
{
    public function countFiltered(array $filters = []): int;

    public function getOverview(int $page = 1, int $perPage = 10, array $filters = []): array;
}