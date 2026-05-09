<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

final class TransactionRepository
{
    public function __construct(
        private readonly Database $database
    ) {
    }

    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO transactions (user_id, product_id, quantity, unit_price, total_amount, transaction_type) VALUES (:user_id, :product_id, :quantity, :unit_price, :total_amount, :transaction_type)',
            [
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'total_amount' => $data['total_amount'],
                'transaction_type' => $data['transaction_type'] ?? 'PURCHASE',
            ]
        );

        return (int) $this->database->lastInsertId();
    }

    public function listAll(): array
    {
        return $this->database->query(
            'SELECT id, user_id, product_id, quantity, unit_price, total_amount, transaction_type, created_at FROM transactions ORDER BY created_at DESC'
        )->fetchAll();
    }

    public function countFilteredWithDetails(array $filters = []): int
    {
        $bindings = [];
        $whereClause = $this->filterClause($filters, $bindings);
        $statement = $this->database->query(
            'SELECT COUNT(*) AS total FROM transactions INNER JOIN users ON users.id = transactions.user_id INNER JOIN products ON products.id = transactions.product_id' . $whereClause,
            $bindings
        );
        $result = $statement->fetch();

        return (int) ($result['total'] ?? 0);
    }

    public function summarizeFilteredWithDetails(array $filters = []): array
    {
        $bindings = [];
        $whereClause = $this->filterClause($filters, $bindings);
        $statement = $this->database->query(
            'SELECT COUNT(*) AS total_transactions, COALESCE(SUM(transactions.quantity), 0) AS total_quantity, COALESCE(SUM(transactions.total_amount), 0) AS total_revenue, COUNT(DISTINCT transactions.user_id) AS unique_users FROM transactions INNER JOIN users ON users.id = transactions.user_id INNER JOIN products ON products.id = transactions.product_id' . $whereClause,
            $bindings
        );
        $result = $statement->fetch();

        return $result === false ? [] : $result;
    }

    public function listFilteredWithDetails(array $filters = [], int $page = 1, int $perPage = 10): array
    {
        $bindings = [];
        $whereClause = $this->filterClause($filters, $bindings);
        $safePerPage = max(1, $perPage);
        $offset = (max(1, $page) - 1) * $safePerPage;

        return $this->database->query(
            'SELECT transactions.id, transactions.user_id, transactions.product_id, transactions.quantity, transactions.unit_price, transactions.total_amount, transactions.transaction_type, transactions.created_at, users.username, products.name AS product_name FROM transactions INNER JOIN users ON users.id = transactions.user_id INNER JOIN products ON products.id = transactions.product_id' . $whereClause . ' ORDER BY transactions.created_at DESC LIMIT :limit OFFSET :offset',
            array_merge($bindings, [
                'limit' => $safePerPage,
                'offset' => $offset,
            ])
        )->fetchAll();
    }

    private function filterClause(array $filters, array &$bindings): string
    {
        $conditions = [];

        $transactionType = trim((string) ($filters['transaction_type'] ?? ''));
        if ($transactionType !== '') {
            $conditions[] = 'transactions.transaction_type = :transaction_type';
            $bindings['transaction_type'] = $transactionType;
        }

        $username = trim((string) ($filters['username'] ?? ''));
        if ($username !== '') {
            $conditions[] = 'users.username LIKE :username';
            $bindings['username'] = '%' . $username . '%';
        }

        $productName = trim((string) ($filters['product_name'] ?? ''));
        if ($productName !== '') {
            $conditions[] = 'products.name LIKE :product_name';
            $bindings['product_name'] = '%' . $productName . '%';
        }

        if ($conditions === []) {
            return '';
        }

        return ' WHERE ' . implode(' AND ', $conditions);
    }
}