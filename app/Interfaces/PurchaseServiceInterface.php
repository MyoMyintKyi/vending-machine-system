<?php

declare(strict_types=1);

namespace App\Interfaces;

interface PurchaseServiceInterface
{
    public function purchase(int $userId, int $productId, int $quantity): array;
}