<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;

#[Signature('bruno:admin
    {username : Login-Name für das Backend}
    {--password= : Passwort direkt angeben (für Konsolen ohne Eingabe, z. B. Laravel Cloud) – danach im Admin unter „Konto“ ändern}
    {--password-hash= : Vorhandenen bcrypt-Hash übernehmen, z. B. aus der alten users-Tabelle}')]
#[Description('Legt einen Admin-Zugang an oder setzt dessen Passwort neu')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = trim((string) $this->argument('username'));
        $hash = $this->option('password-hash');

        if (! preg_match('/^[\p{L}\d._-]{3,60}$/u', $username)) {
            $this->error('Der Benutzername muss 3–60 Zeichen lang sein (Buchstaben, Zahlen, . _ -).');

            return self::FAILURE;
        }

        if ($hash !== null && ! str_starts_with($hash, '$2y$')) {
            $this->error('Der Hash muss ein bcrypt-Hash sein ($2y$...).');

            return self::FAILURE;
        }

        $plain = $this->option('password');

        if ($plain !== null && mb_strlen($plain) < 8) {
            $this->error('Das Passwort muss mindestens 8 Zeichen lang sein.');

            return self::FAILURE;
        }

        $password = $hash ?? Hash::make($plain ?? password(
            label: 'Passwort',
            required: true,
            validate: fn (string $value): ?string => mb_strlen($value) < 8 ? 'Mindestens 8 Zeichen.' : null,
        ));

        $user = User::query()->firstOrNew(['username' => $username]);
        $user->password = $password;
        $user->save();

        $this->info($user->wasRecentlyCreated
            ? "Admin '{$username}' angelegt."
            : "Passwort für '{$username}' aktualisiert.");

        return self::SUCCESS;
    }
}
