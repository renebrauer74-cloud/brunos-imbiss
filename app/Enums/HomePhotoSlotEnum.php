<?php

namespace App\Enums;

/**
 * Die drei Plätze für Fotos unter der Speisekarte.
 */
enum HomePhotoSlotEnum: int
{
    case First = 1;
    case Second = 2;
    case Third = 3;

    public function defaultCaption(): string
    {
        return match ($this) {
            self::First => 'Currywurst Pommes',
            self::Second => 'Frisch aus der Fritteuse',
            self::Third => 'Schnitzel satt',
        };
    }

    /** Platzhalter, solange kein Foto hochgeladen ist */
    public function emoji(): string
    {
        return match ($this) {
            self::First => '🌭',
            self::Second => '🍟',
            self::Third => '🍖',
        };
    }
}
