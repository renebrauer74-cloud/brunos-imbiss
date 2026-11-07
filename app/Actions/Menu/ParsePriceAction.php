<?php

namespace App\Actions\Menu;

/**
 * "3,50" / "3.5" / "3,50 €" → 350 (Cent), null bei ungültiger Eingabe.
 */
class ParsePriceAction
{
    public function execute(?string $input): ?int
    {
        $value = trim(str_replace(['€', ' ', "\u{00A0}"], '', (string) $input));

        if ($value === '') {
            return null;
        }

        // Tausenderpunkt entfernen, wenn zusätzlich ein Komma vorkommt (1.234,50)
        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        $value = str_replace(',', '.', $value);

        if (! preg_match('/^\d{1,5}(\.\d{1,2})?$/', $value)) {
            return null;
        }

        return (int) round(((float) $value) * 100);
    }
}
