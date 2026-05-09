<?php

declare(strict_types=1);

namespace App\Repositories;

use Core\Database;

final class ProductRepository
{
    public function __construct(
        private readonly Database $database
    ) {
    }

    public function create(array $data): int
    {
        $this->database->query(
            'INSERT INTO products (name, price, quantity_available) VALUES (:name, :price, :quantity_available)',
            [
                'name' => $data['name'],
                'price' => $data['price'],
                'quantity_available' => $data['quantity_available'],
            ]
        );

        return (int) $this->database->lastInsertId();
    }

    public function findById(int $id): ?array
    {
        $statement = $this->database->query(
            'SELECT id, name, price, quantity_available, created_at, updated_at FROM products WHERE id = :id LIMIT 1',
            ['id' => $id]
        );

        $product = $statement->fetch();

        return $product === false ? null : $product;
    }

    public function findAll(int $page = 1, int $perPage = 10, string $sortBy = 'name', string $direction = 'asc'): array
    {
        $allowedSorts = [
            'name' => 'name',
            'price' => 'price',
            'quantity_available' => 'quantity_available',
        ];

        $sortColumn = $allowedSorts[$sortBy] ?? 'name';
        $sortDirection = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $offset = max($page, 1) - 1;
        $offset *= max($perPage, 1);

        $statement = $this->database->query(
            sprintf(
                'SELECT id, name, price, quantity_available, created_at, updated_at FROM products ORDER BY %s %s LIMIT :limit OFFSET :offset',
                $sortColumn,
                $sortDirection
            ),
            [
                'limit' => max($perPage, 1),
                'offset' => $offset,
            ]
        );

        return $statement->fetchAll();
    }

    public function countAll(): int
    {
        $statement = $this->database->query('SELECT COUNT(*) AS total FROM products');
        $result = $statement->fetch();

        return (int) ($result['total'] ?? 0);
    }

    public function update(int $id, array $data): bool
    {
        return $this->database->query(
            'UPDATE products SET name = :name, price = :price, quantity_available = :quantity_available WHERE id = :id',
            [
                'id' => $id,
                'name' => $data['name'],
                'price' => $data['price'],
                'quantity_available' => $data['quantity_available'],
            ]
        )->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        return $this->database->query(
            'DELETE FROM products WHERE id = :id',
            ['id' => $id]
        )->rowCount() > 0;
    }

    public function decrementStock(int $id, int $quantity): bool
    {
        return $this->database->query(
            'UPDATE products SET quantity_available = quantity_available - :decrement_quantity WHERE id = :id AND quantity_available >= :minimum_available_quantity',
            [
                'id' => $id,
                'decrement_quantity' => $quantity,
                'minimum_available_quantity' => $quantity,
            ]
        )->rowCount() > 0;
    }
}