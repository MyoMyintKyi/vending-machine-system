<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ProductServiceInterface;
use App\Repositories\ProductRepository;

final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function findAll(int $page = 1, int $perPage = 10, string $sortBy = 'name', string $direction = 'asc'): array
    {
        return $this->productRepository->findAll($page, $perPage, $sortBy, $direction);
    }

    public function countAll(): int
    {
        return $this->productRepository->countAll();
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        return $this->productRepository->findById($id);
    }

    public function create(array $data): int
    {
        return $this->productRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->productRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->productRepository->delete($id);
    }
}