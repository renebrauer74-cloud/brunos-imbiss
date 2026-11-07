<?php

namespace App\Enums;

use Carbon\CarbonInterface;

/**
 * Wochentage der Öffnungszeiten. Der Wert ist der Schlüssel im JSON der Spalte site_settings.opening_hours.
 */
enum WeekdayEnum: string
{
    case Monday = 'mo';
    case Tuesday = 'di';
    case Wednesday = 'mi';
    case Thursday = 'do';
    case Friday = 'fr';
    case Saturday = 'sa';
    case Sunday = 'so';

    public static function fromDate(CarbonInterface $date): self
    {
        return self::cases()[$date->dayOfWeekIso - 1];
    }

    public function label(): string
    {
        return match ($this) {
            self::Monday => 'Montag',
            self::Tuesday => 'Dienstag',
            self::Wednesday => 'Mittwoch',
            self::Thursday => 'Donnerstag',
            self::Friday => 'Freitag',
            self::Saturday => 'Samstag',
            self::Sunday => 'Sonntag',
        };
    }

    public function short(): string
    {
        return ucfirst($this->value);
    }

    /** Name für schema.org (dayOfWeek) */
    public function schemaName(): string
    {
        return $this->name;
    }

    public function next(int $days = 1): self
    {
        $index = array_search($this, self::cases(), true);

        return self::cases()[($index + $days) % 7];
    }
}
