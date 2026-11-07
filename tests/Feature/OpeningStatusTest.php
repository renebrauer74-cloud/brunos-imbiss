<?php

use App\Actions\Site\DescribeOpeningStatusAction;
use App\Models\SiteSetting;
use Illuminate\Support\Carbon;

function settingsWithHours(array $hours): SiteSetting
{
    $settings = app(SiteSetting::class);
    $settings->update(['opening_hours' => $hours]);

    return $settings;
}

it('returns no status without opening hours', function () {
    expect(app(DescribeOpeningStatusAction::class)->execute(app(SiteSetting::class)))->toBeNull();
});

it('describes the opening status', function (string $now, bool $open, string $text) {
    $settings = settingsWithHours([
        'mo' => ['closed' => false, 'from' => '11:00', 'to' => '20:00'],
        'di' => ['closed' => true, 'from' => '', 'to' => ''],
        'mi' => ['closed' => true, 'from' => '', 'to' => ''],
        'do' => ['closed' => false, 'from' => '11:00', 'to' => '20:00'],
        'fr' => ['closed' => false, 'from' => '18:00', 'to' => '02:00'],
        'sa' => ['closed' => false, 'from' => '', 'to' => ''],
        'so' => ['closed' => true, 'from' => '', 'to' => ''],
    ]);

    // 2026-11-09 ist ein Montag
    expect(app(DescribeOpeningStatusAction::class)->execute($settings, Carbon::parse($now)))
        ->toBe(['open' => $open, 'text' => $text]);
})->with([
    'open on monday' => ['2026-11-09 12:30', true, 'Jetzt geöffnet · bis 20:00 Uhr'],
    'before opening' => ['2026-11-09 09:00', false, 'Geschlossen · öffnet heute um 11:00 Uhr'],
    'after closing, closed tomorrow' => ['2026-11-09 21:00', false, 'Geschlossen · öffnet Donnerstag um 11:00 Uhr'],
    'closed day before open day' => ['2026-11-11 10:00', false, 'Geschlossen · öffnet morgen um 11:00 Uhr'],
    'after midnight from friday' => ['2026-11-14 01:30', true, 'Jetzt geöffnet · bis 02:00 Uhr'],
    'friday night' => ['2026-11-13 23:00', true, 'Jetzt geöffnet · bis 02:00 Uhr'],
]);
