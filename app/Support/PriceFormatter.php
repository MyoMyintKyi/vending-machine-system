<?php

declare(strict_types=1);

namespace App\Support;

final class PriceFormatter
{
    public static function normalize(int|float|string $value, int $scale = 3): string
    {
        $formatted = number_format((float) $value, $scale, '.', '');
        $trimmed = rtrim(rtrim($formatted, '0'), '.');

        return $trimmed === '' ? '0' : $trimmed;
    }
}