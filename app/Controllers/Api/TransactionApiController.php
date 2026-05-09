<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Interfaces\TransactionServiceInterface;
use App\Repositories\TransactionRepository;
use App\Support\CurrencyFormatter;
use App\Services\TransactionService;
use Core\Database;
use Core\Request;
use Core\Response;

final class TransactionApiController
{
    public function __construct(
        private readonly ?TransactionServiceInterface $transactionService = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));
        $filters = $this->filters($request);
        $service = $this->service();
        $total = $service->countFiltered($filters);
        $overview = $service->getOverview($page, $perPage, $filters);

        $response->json([
            'success' => true,
            'data' => [
                'items' => array_map($this->formatTransaction(...), (array) ($overview['transactions'] ?? [])),
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'total_pages' => max(1, (int) ceil($total / $perPage)),
                ],
                'filters' => $filters,
            ],
            'message' => 'Transactions retrieved successfully.',
        ]);
    }

    private function formatTransaction(array $transaction): array
    {
        if (array_key_exists('unit_price', $transaction)) {
            $transaction['unit_price'] = CurrencyFormatter::formatUsd((string) $transaction['unit_price']);
        }

        if (array_key_exists('total_amount', $transaction)) {
            $transaction['total_amount'] = CurrencyFormatter::formatUsd((string) $transaction['total_amount']);
        }

        return $transaction;
    }

    private function filters(Request $request): array
    {
        $transactionType = trim((string) $request->query('transaction_type', ''));
        $allowedTypes = ['PURCHASE'];

        if (!in_array($transactionType, $allowedTypes, true)) {
            $transactionType = '';
        }

        return [
            'transaction_type' => $transactionType,
            'username' => trim((string) $request->query('username', '')),
            'product_name' => trim((string) $request->query('product_name', '')),
        ];
    }

    private function service(): TransactionServiceInterface
    {
        if ($this->transactionService instanceof TransactionServiceInterface) {
            return $this->transactionService;
        }

        $database = new Database(require config_path('database.php'));

        return new TransactionService(new TransactionRepository($database));
    }
}