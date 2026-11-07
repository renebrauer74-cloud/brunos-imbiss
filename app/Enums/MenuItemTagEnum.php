<?php

namespace App\Enums;

/**
 * Kennzeichnungen an einem Gericht. Der Wert ist zugleich die CSS-Klasse (tag-scharf …).
 */
enum MenuItemTagEnum: string
{
    case Spicy = 'scharf';
    case New = 'neu';
    case Vegetarian = 'vegetarisch';
    case Popular = 'beliebt';

    public function label(): string
    {
        return match ($this) {
            self::Spicy => 'Scharf',
            self::New => 'Neu',
            self::Vegetarian => 'Vegetarisch',
            self::Popular => 'Beliebt',
        };
    }
}
