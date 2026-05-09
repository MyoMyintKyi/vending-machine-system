<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\CurrencyFormatter;
use PHPUnit\Framework\TestCase;

final class PriceFormatterTest extends TestCase
{
    public function testFormatUsdAppendsCurrencySuffix(): void
    {
        $this->assertSame('2,000.15 USD', CurrencyFormatter::formatUsd('2000.15'));
    }
}