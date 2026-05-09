<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Interfaces\TransactionServiceInterface;
use App\Repositories\TransactionRepository;
use App\Services\TransactionService;
use Core\Database;
use Core\Request;
use Core\Response;

final class TransactionController
{
    public function __construct(
        private readonly ?TransactionServiceInterface $transactionService = null
    ) {
    }

    public function index(Request $request, Response $response): void
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $filters = $this->filters($request);
        $totalTransactions = $this->service()->countFiltered($filters);
        $totalPages = max(1, (int) ceil($totalTransactions / $perPage));
        $page = min($page, $totalPages);
        $overview = $this->service()->getOverview($page, $perPage, $filters);

        $response->view('transactions/index', [
            'title' => 'Transactions Module',
            'flash' => (string) $request->pullSessionValue('flash', ''),
            'role' => (string) $request->session('role', ''),
            'metrics' => (array) ($overview['metrics'] ?? []),
            'transactions' => (array) ($overview['transactions'] ?? []),
            'filters' => $filters,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
            'hasPreviousPage' => $page > 1,
            'hasNextPage' => $page < $totalPages,
        ]);
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