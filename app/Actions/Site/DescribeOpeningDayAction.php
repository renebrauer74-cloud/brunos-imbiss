<?php

namespace App\Actions\Site;

/**
 * Anzeige eines Tages: "11:00 – 20:00 Uhr", "Ruhetag" oder "–".
 */
class DescribeOpeningDayAction
{
    /**
     * @param  array{closed: bool, from: string, to: string}  $day
     */
    public function execute(array $day): string
    {
        if ($day['closed']) {
            return 'Ruhetag';
        }

        if ($day['from'] === '' || $day['to'] === '') {
            return '–';
        }

        return $day['from'].' – '.$day['to'].' Uhr';
    }
}
