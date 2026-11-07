<?php

namespace App\Actions\Site;

use App\Enums\WeekdayEnum;
use App\Models\SiteSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

/**
 * Live-Status für die Startseite, z. B. "Jetzt geöffnet · bis 20:00 Uhr".
 * Berücksichtigt Zeiten über Mitternacht (z. B. 18:00 – 02:00).
 */
class DescribeOpeningStatusAction
{
    public function __construct(
        private ListOpeningHoursAction $listOpeningHours,
        private CheckOpeningHoursAction $checkOpeningHours,
    ) {}

    /**
     * @return array{open: bool, text: string}|null null, wenn keine Öffnungszeiten gepflegt sind
     */
    public function execute(SiteSetting $settings, ?CarbonInterface $now = null): ?array
    {
        if (! $this->checkOpeningHours->execute($settings)) {
            return null;
        }

        $hours = $this->listOpeningHours->execute($settings);
        $now ??= Date::now();
        $today = WeekdayEnum::fromDate($now);
        $minutes = $now->hour * 60 + $now->minute;

        $yesterday = $hours[$today->next(6)->value];
        if ($this->isOpenDay($yesterday) && $this->overnight($yesterday) && $minutes < $this->toMinutes($yesterday['to'])) {
            return ['open' => true, 'text' => 'Jetzt geöffnet · bis '.$yesterday['to'].' Uhr'];
        }

        $current = $hours[$today->value];
        if ($this->isOpenDay($current)) {
            $from = $this->toMinutes($current['from']);

            if ($minutes >= $from && ($this->overnight($current) || $minutes < $this->toMinutes($current['to']))) {
                return ['open' => true, 'text' => 'Jetzt geöffnet · bis '.$current['to'].' Uhr'];
            }

            if ($minutes < $from) {
                return ['open' => false, 'text' => 'Geschlossen · öffnet heute um '.$current['from'].' Uhr'];
            }
        }

        foreach (range(1, 7) as $offset) {
            $day = $today->next($offset);

            if ($this->isOpenDay($hours[$day->value])) {
                $when = $offset === 1 ? 'morgen' : $day->label();

                return ['open' => false, 'text' => 'Geschlossen · öffnet '.$when.' um '.$hours[$day->value]['from'].' Uhr'];
            }
        }

        return ['open' => false, 'text' => 'Derzeit geschlossen'];
    }

    /**
     * @param  array{closed: bool, from: string, to: string}  $day
     */
    private function isOpenDay(array $day): bool
    {
        return ! $day['closed'] && $day['from'] !== '' && $day['to'] !== '';
    }

    /**
     * @param  array{closed: bool, from: string, to: string}  $day
     */
    private function overnight(array $day): bool
    {
        return $this->toMinutes($day['to']) <= $this->toMinutes($day['from']);
    }

    private function toMinutes(string $time): int
    {
        return (int) substr($time, 0, 2) * 60 + (int) substr($time, 3, 2);
    }
}
