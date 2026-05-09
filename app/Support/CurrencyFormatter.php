<?php

declare(strict_types=1);

namespace App\Support;

final class CurrencyFormatter
{
    public static function formatUsd(int|float|string|null $value): string
    {
        return ViewNumberFormatter::format($value) . ' USD';
    }
}