<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Speisekarte laut Preisliste anlegen. Einstellungen und Startseiten-Fotos legen die Migrationen an,
     * den Admin-Zugang `php artisan bruno:admin <name>`.
     */
    public function run(): void
    {
        $this->call(MenuSeeder::class);
    }
}
