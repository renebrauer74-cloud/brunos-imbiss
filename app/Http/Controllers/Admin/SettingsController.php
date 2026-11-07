<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Site\ListOpeningHoursAction;
use App\Enums\WeekdayEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Einstellungen: Texte, Kontakt, Öffnungszeiten, Rechtliches.
 */
class SettingsController extends Controller
{
    public function edit(SiteSetting $settings, ListOpeningHoursAction $listOpeningHours): View
    {
        return view('admin.settings', [
            'settings' => $settings,
            'hours' => $listOpeningHours->execute($settings),
        ]);
    }

    public function update(SettingsRequest $request, SiteSetting $settings): RedirectResponse
    {
        $hours = [];
        foreach (WeekdayEnum::cases() as $day) {
            $hours[$day->value] = [
                'closed' => $request->boolean("hours.{$day->value}.closed"),
                'from' => (string) $request->input("hours.{$day->value}.from"),
                'to' => (string) $request->input("hours.{$day->value}.to"),
            ];
        }

        $settings->update([
            ...$request->safe()->except('hours'),
            'opening_hours' => $hours,
        ]);

        return to_route('admin.settings.edit')->with('status', 'Einstellungen gespeichert.');
    }
}
