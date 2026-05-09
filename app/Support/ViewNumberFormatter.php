<?php

declare(strict_types=1);

namespace App\Support;

final class ViewNumberFormatter
{
    public static function format(int|float|string|null $value): string
    {
        if ($value === null) {
            return '0';
        }

        $stringValue = trim((string) $value);

        if ($stringValue === '') {
            return '0';
        }

        if (!preg_match('/^-?\d+(?:\.\d+)?$/', $stringValue)) {
            return $stringValue;
        }

        $negative = str_starts_with($stringValue, '-');
        $unsignedValue = $negative ? substr($stringValue, 1) : $stringValue;
        [$integerPart, $decimalPart] = array_pad(explode('.', $unsignedValue, 2), 2, null);
        $formattedInteger = number_format((int) $integerPart, 0, '.', ',');

        return ($negative ? '-' : '') . $formattedInteger . ($decimalPart !== null ? '.' . $decimalPart : '');
    }
}