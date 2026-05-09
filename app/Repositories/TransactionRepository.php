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

    public function listByUser(int $userId): array
    {
        return $this->database->query(
            'SELECT id, user_id, product_id, quantity, unit_price, total_amount, transaction_type, created_at FROM transactions WHERE user_id = :user_id ORDER BY created_at DESC',
            ['user_id' => $userId]
        )->fetchAll();
    }

    public function listAll(): array
    {
        return $this->database->query(
            'SELECT id, user_id, product_id, quantity, unit_price, total_amount, transaction_type, created_at FROM transactions ORDER BY created_at DESC'
        )->fetchAll();
    }
}