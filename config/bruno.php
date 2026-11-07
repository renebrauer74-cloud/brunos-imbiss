<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bild-Uploads
    |--------------------------------------------------------------------------
    |
    | Maximale Größe in Kilobyte. Muss zu upload_max_filesize / post_max_size
    | in public/.user.ini passen. Große Handyfotos verkleinert das Admin-
    | JavaScript schon vor dem Upload.
    |
    */

    'uploads' => [
        'max_kilobytes' => (int) env('UPLOAD_MAX_KILOBYTES', 20 * 1024),
    ],

    /*
    |--------------------------------------------------------------------------
    | Datenübernahme aus der alten Plain-PHP-Version
    |--------------------------------------------------------------------------
    |
    | Ordner mit den Bildern der alten Seite (dort: uploads/). Die Datenbank
    | der alten Seite ist die Verbindung "legacy" in config/database.php.
    |
    */

    'legacy' => [
        'uploads_path' => env('LEGACY_UPLOADS_PATH'),

        // Mitgelieferter Stand für `bruno:import-legacy --snapshot` (SQLite im alten Schema + Bilder, ohne Passwörter)
        'snapshot_path' => database_path('snapshot'),
    ],

];
