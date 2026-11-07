<?php

use App\Models\SiteSetting;

/**
 * @return array<string, array{from: string, to: string, closed?: string}>
 */
function weekHours(): array
{
    return [
        'mo' => ['from' => '11:00', 'to' => '20:00'],
        'di' => ['from' => '11:00', 'to' => '20:00'],
        'mi' => ['from' => '11:00', 'to' => '20:00'],
        'do' => ['from' => '11:00', 'to' => '20:00'],
        'fr' => ['from' => '11:00', 'to' => '22:00'],
        'sa' => ['from' => '12:00', 'to' => '22:00'],
        'so' => ['from' => '', 'to' => '', 'closed' => '1'],
    ];
}

it('shows the settings', function () {
    actingAsAdmin()->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee('Öffnungszeiten')
        ->assertSee("Bruno's Imbiss");
});

it('saves the settings and opening hours', function () {
    actingAsAdmin()
        ->put(route('admin.settings.update'), [
            'site_title' => "Bruno's Imbiss",
            'street' => 'Kortumstraße 1',
            'zip_city' => '44787 Bochum',
            'phone' => '0234 123456',
            'email' => 'info@example.com',
            'hours' => weekHours(),
        ])
        ->assertRedirect(route('admin.settings.edit'))
        ->assertSessionHas('status', 'Einstellungen gespeichert.');

    $settings = SiteSetting::query()->sole();

    expect($settings->street)->toBe('Kortumstraße 1')
        ->and($settings->opening_hours['fr'])->toBe(['closed' => false, 'from' => '11:00', 'to' => '22:00'])
        ->and($settings->opening_hours['so']['closed'])->toBeTrue();
});

it('validates email and opening hours', function () {
    $hours = weekHours();
    $hours['mo'] = ['from' => '11:00', 'to' => ''];
    $hours['di'] = ['from' => '25:00', 'to' => '20:00'];

    actingAsAdmin()
        ->put(route('admin.settings.update'), [
            'site_title' => "Bruno's Imbiss",
            'email' => 'keine-mail',
            'hours' => $hours,
        ])
        ->assertSessionHasErrors(['email', 'hours.mo', 'hours.di.from']);
});
