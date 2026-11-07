<?php

namespace App\Console\Commands;

use App\Actions\Legacy\ImportLegacyDataAction;
use App\Models\Category;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

#[Signature('bruno:import-legacy
    {--uploads= : Ordner mit den Bildern der alten Seite (Standard: LEGACY_UPLOADS_PATH)}
    {--snapshot : Mitgelieferten Stand aus database/snapshot/ übernehmen (z. B. auf Laravel Cloud)}')]
#[Description('Übernimmt Speisekarte, Einstellungen, Bilder und Admin-Zugang aus der alten Plain-PHP-Version (Verbindung "legacy")')]
class ImportLegacyData extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ImportLegacyDataAction $importLegacyData): int
    {
        if (Category::query()->exists()) {
            $this->error('Die Speisekarte enthält bereits Daten. Import nur in eine frische Datenbank (php artisan migrate:fresh).');

            return self::FAILURE;
        }

        $uploadsPath = $this->option('uploads') ?? config('bruno.legacy.uploads_path');

        if ($this->option('snapshot')) {
            $database = config('bruno.legacy.snapshot_path').'/bruno-snapshot.db';

            if (! is_file($database)) {
                $this->error("Kein Snapshot unter '{$database}' gefunden.");

                return self::FAILURE;
            }

            config(['database.connections.legacy' => [
                'driver' => 'sqlite',
                'database' => $database,
                'prefix' => '',
                'foreign_key_constraints' => false,
            ]]);
            DB::purge('legacy');
            $uploadsPath = config('bruno.legacy.snapshot_path').'/uploads';
        }

        if ($uploadsPath !== null && ! is_dir($uploadsPath)) {
            $this->error("Der Bilder-Ordner '{$uploadsPath}' existiert nicht.");

            return self::FAILURE;
        }

        try {
            $result = $importLegacyData->execute(DB::connection('legacy'), $uploadsPath);
        } catch (Throwable $exception) {
            $this->error('Import fehlgeschlagen: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Übernommen: {$result['categories']} Kategorien, {$result['items']} Artikel, {$result['gallery']} Galeriebilder, {$result['users']} Admin-Zugang/-Zugänge, {$result['images']} Bilddateien.");

        if ($uploadsPath === null) {
            $this->warn('Ohne --uploads bzw. LEGACY_UPLOADS_PATH wurden keine Bilddateien übernommen.');
        }

        foreach ($result['missing_images'] as $file) {
            $this->warn("Bild nicht gefunden: {$file}");
        }

        if ($result['users'] === 0) {
            $this->warn('Noch kein Admin-Zugang: php artisan bruno:admin <name> --password=<passwort>');
        }

        return self::SUCCESS;
    }
}
