<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\PurchaseServiceInterface;
use App\Repositories\ProductRepository;
use App\Repositories\TransactionRepository;
use App\Support\PriceFormatter;
use Core\Database;
use DomainException;
use Throwable;

final class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(
        private readonly Database $database,
        private readonly ProductRepository $productRepository,
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function purchase(int $userId, int $productId, int $quantity): array
    {
        if ($userId <= 0) {
            throw new DomainException('You must be logged in to purchase a product.');
        }

        if ($quantity < 1) {
            throw new DomainException('Purchase quantity must be at least 1.');
        }

        $product = $this->productRepository->findById($productId);

        if ($product === null) {
            throw new DomainException('Product not found.');
        }

        $unitPrice = PriceFormatter::normalize((string) $product['price']);
        $totalAmount = PriceFormatter::normalize((float) $unitPrice * $quantity);
        $availableQuantity = (int) $product['quantity_available'];

        if ($availableQuantity < $quantity) {
            throw new DomainException('Requested quantity exceeds available stock.');
        }

        $updatedQuantityAvailable = $availableQuantity - $quantity;

        $this->database->beginTransaction();

        try {
            $decremented = $this->productRepository->decrementStock($productId, $quantity);

            if (!$decremented) {
                throw new DomainException('Product stock could not be updated.');
            }

            $transactionId = $this->transactionRepository->create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'transaction_type' => 'PURCHASE',
            ]);

            $this->database->commit();

            return [
                'transaction_id' => $transactionId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'quantity_available' => $updatedQuantityAvailable,
            ];
        } catch (Throwable $throwable) {
            $this->database->rollBack();

            if ($throwable instanceof DomainException) {
                throw $throwable;
            }

            throw new DomainException('Purchase could not be completed. Please try again.');
        }
    }
}