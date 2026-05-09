<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\TransactionServiceInterface;
use App\Repositories\TransactionRepository;
use App\Support\PriceFormatter;

final class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function countFiltered(array $filters = []): int
    {
        return $this->transactionRepository->countFilteredWithDetails($filters);
    }

    public function getOverview(int $page = 1, int $perPage = 10, array $filters = []): array
    {
        $summary = $this->transactionRepository->summarizeFilteredWithDetails($filters);
        $transactions = array_map(
            fn (array $transaction): array => $this->normalizeTransaction($transaction),
            $this->transactionRepository->listFilteredWithDetails($filters, $page, $perPage)
        );

        return [
            'metrics' => [
                'total_transactions' => (int) ($summary['total_transactions'] ?? 0),
                'total_quantity' => (int) ($summary['total_quantity'] ?? 0),
                'total_revenue' => PriceFormatter::normalize((string) ($summary['total_revenue'] ?? '0')),
                'unique_users' => (int) ($summary['unique_users'] ?? 0),
            ],
            'transactions' => $transactions,
        ];
    }

    private function normalizeTransaction(array $transaction): array
    {
        if (array_key_exists('unit_price', $transaction)) {
            $transaction['unit_price'] = PriceFormatter::normalize((string) $transaction['unit_price']);
        }

        if (array_key_exists('total_amount', $transaction)) {
            $transaction['total_amount'] = PriceFormatter::normalize((string) $transaction['total_amount']);
        }

        return $transaction;
    }
}