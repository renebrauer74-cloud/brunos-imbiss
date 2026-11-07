<?php

namespace App\Actions\Site;

use App\Models\SiteSetting;

/**
 * Sind überhaupt Öffnungszeiten gepflegt? (Mindestens ein Tag mit Zeiten oder Ruhetag.)
 */
class CheckOpeningHoursAction
{
    public function __construct(private ListOpeningHoursAction $listOpeningHours) {}

    public function execute(SiteSetting $settings): bool
    {
        foreach ($this->listOpeningHours->execute($settings) as $day) {
            if ($day['closed'] || ($day['from'] !== '' && $day['to'] !== '')) {
                return true;
            }
        }

        return false;
    }
}
