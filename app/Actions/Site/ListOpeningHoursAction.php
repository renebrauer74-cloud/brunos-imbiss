<?php

namespace App\Actions\Site;

use App\Enums\WeekdayEnum;
use App\Models\SiteSetting;

/**
 * Öffnungszeiten aller sieben Tage – fehlende Tage werden mit leeren Zeiten ergänzt.
 */
class ListOpeningHoursAction
{
    /**
     * @return array<string, array{closed: bool, from: string, to: string}> Schlüssel = WeekdayEnum-Wert
     */
    public function execute(SiteSetting $settings): array
    {
        $stored = is_array($settings->opening_hours) ? $settings->opening_hours : [];
        $hours = [];

        foreach (WeekdayEnum::cases() as $day) {
            $entry = is_array($stored[$day->value] ?? null) ? $stored[$day->value] : [];

            $hours[$day->value] = [
                'closed' => (bool) ($entry['closed'] ?? false),
                'from' => (string) ($entry['from'] ?? ''),
                'to' => (string) ($entry['to'] ?? ''),
            ];
        }

        return $hours;
    }
}
