<?php

namespace App\Actions\Menu;

/**
 * 350 (Cent) → "3,50 €"
 */
class FormatPriceAction
{
    public function execute(int $cents, bool $withCurrency = true): string
    {
        $price = number_format($cents / 100, 2, ',', $withCurrency ? '.' : '');

        return $withCurrency ? $price.' €' : $price;
    }
}
