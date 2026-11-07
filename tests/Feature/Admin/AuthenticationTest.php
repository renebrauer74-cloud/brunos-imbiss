<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('redirects guests from the backend to the login', function (string $url) {
    $this->get($url)->assertRedirect(route('admin.login'));
})->with(['/admin', '/admin/favorites', '/admin/images', '/admin/settings', '/admin/account']);

it('shows the login page', function () {
    $this->get(route('admin.login'))->assertOk()->assertSee('Anmelden');
});

it('logs an admin in with valid credentials', function () {
    User::factory()->create(['username' => 'bruno', 'password' => 'richtig-geheim']);

    $this->post(route('admin.login.store'), ['username' => 'bruno', 'password' => 'richtig-geheim'])
        ->assertRedirect(route('admin.menu.index'));

    $this->assertAuthenticated();
});

it('rejects wrong credentials', function () {
    User::factory()->create(['username' => 'bruno', 'password' => 'richtig-geheim']);

    $this->from(route('admin.login'))
        ->post(route('admin.login.store'), ['username' => 'bruno', 'password' => 'falsch'])
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors(['username' => 'Benutzername oder Passwort ist falsch.']);

    $this->assertGuest();
});

it('locks the login after five failed attempts', function () {
    User::factory()->create(['username' => 'bruno', 'password' => 'richtig-geheim']);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('admin.login.store'), ['username' => 'bruno', 'password' => 'falsch']);
    }

    $this->post(route('admin.login.store'), ['username' => 'bruno', 'password' => 'richtig-geheim'])
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

it('logs the admin out', function () {
    actingAsAdmin()
        ->post(route('admin.logout'))
        ->assertRedirect(route('admin.login'));

    $this->assertGuest();
});

it('changes the password', function () {
    $user = User::factory()->create(['password' => 'altes-passwort']);

    $this->actingAs($user)
        ->put(route('admin.account.update'), [
            'current_password' => 'altes-passwort',
            'password' => 'neues-passwort',
            'password_confirmation' => 'neues-passwort',
        ])
        ->assertRedirect(route('admin.account.edit'))
        ->assertSessionHas('status', 'Passwort geändert.');

    expect(Hash::check('neues-passwort', $user->fresh()->password))->toBeTrue();
});

it('requires the current password to change it', function () {
    $user = User::factory()->create(['password' => 'altes-passwort']);

    $this->actingAs($user)
        ->put(route('admin.account.update'), [
            'current_password' => 'falsch',
            'password' => 'neues-passwort',
            'password_confirmation' => 'neues-passwort',
        ])
        ->assertSessionHasErrors(['current_password' => 'Das aktuelle Passwort ist falsch.']);

    expect(Hash::check('altes-passwort', $user->fresh()->password))->toBeTrue();
});
