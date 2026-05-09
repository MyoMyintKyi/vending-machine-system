<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ProductServiceInterface;
use App\Repositories\ProductRepository;
use App\Support\PriceFormatter;

final class ProductService implements ProductServiceInterface
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {
    }

    public function findAll(int $page = 1, int $perPage = 10, string $sortBy = 'name', string $direction = 'asc'): array
    {
        return array_map(
            fn (array $product): array => $this->normalizeProduct($product),
            $this->productRepository->findAll($page, $perPage, $sortBy, $direction)
        );
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

        $product = $this->productRepository->findById($id);

        return $product === null ? null : $this->normalizeProduct($product);
    }

    public function create(array $data): int
    {
        $data['price'] = PriceFormatter::normalize((string) ($data['price'] ?? '0'));

        return $this->productRepository->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['price'] = PriceFormatter::normalize((string) ($data['price'] ?? '0'));

        return $this->productRepository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->productRepository->delete($id);
    }

    private function normalizeProduct(array $product): array
    {
        if (array_key_exists('price', $product)) {
            $product['price'] = PriceFormatter::normalize((string) $product['price']);
        }

        return $product;
    }
}