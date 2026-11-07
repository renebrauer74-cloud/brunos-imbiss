# Bruno's Imbiss – Bochum

Website mit Speisekarte, „Unsere Lieblinge“, Fotos, Galerie und Admin-Bereich (`/admin`).
Laravel 13, Spatie Media Library, Pest.

## Lokal (Laravel Herd)

- URL: http://brunoimbisss.test, Admin: http://brunoimbisss.test/admin
- Datenbank: SQLite (`database/database.sqlite`), Bilder: `public/uploads/<collection>/<uuid>/`

```bash
composer install
php artisan migrate --seed        # leere DB mit Speisekarte laut Preisliste
php artisan bruno:admin <name>    # Admin-Zugang anlegen / Passwort setzen
php artisan test --compact
```

### Daten der alten Plain-PHP-Version übernehmen

Verbindung `legacy` und `LEGACY_UPLOADS_PATH` in der `.env` eintragen, dann in eine leere Datenbank:

```bash
php artisan migrate:fresh
php artisan bruno:import-legacy
```

Übernimmt Kategorien, Artikel (gleiche IDs), Lieblinge, Einstellungen, Öffnungszeiten, Startseiten-Fotos,
Galerie und den Admin-Login (bcrypt-Hash, Passwort bleibt gleich). Die alten Bilder werden nur kopiert.

## Live mit Laravel Cloud

### 1. Auf GitHub hochladen (GitKraken)

1. GitKraken → **File → Init Repo → Local Only** → Ordner `D:\Arbeit\bruno\brunos-imbiss` wählen.
   Bei „.gitignore Template“ und „License“ **nichts** auswählen – die `.gitignore` ist schon da.
2. Alle Dateien stagen („Stage all changes“), Commit-Nachricht z. B. `Bruno's Imbiss mit Laravel`, committen.
   `.env`, `vendor/`, `node_modules/` und `public/uploads/` werden durch die `.gitignore` automatisch ausgelassen.
3. Links bei **Remote** auf **+** → GitHub → **Create remote** → Repository `brunos-imbiss`, **Private**.
4. **Push** (Branch `main`).

### 2. Laravel Cloud einrichten

1. cloud.laravel.com → **New application** → GitHub verbinden → Repository `brunos-imbiss`, Branch `main`, Region **Frankfurt**.
2. **Database**: MySQL-Datenbank anlegen und an die Umgebung hängen (Zugangsdaten setzt Cloud automatisch).
3. **Object storage**: Bucket anlegen, Sichtbarkeit **Public**, an die Umgebung hängen mit **Disk name `uploads`**
   („Use as default disk“ aus). Cloud ersetzt damit die lokale Disk `uploads` – die Fotos landen im Bucket.
4. **Settings → Environment variables** ergänzen:
   ```
   APP_NAME="Bruno's Imbiss"
   APP_LOCALE=de
   APP_FAKER_LOCALE=de_DE
   APP_TIMEZONE=Europe/Berlin
   QUEUE_CONNECTION=sync
   MEDIA_DISK=uploads
   UPLOAD_MAX_KILOBYTES=20480
   ```
5. **Deploy commands**: `php artisan migrate --force` (Build-Befehle können so bleiben).
6. **Deploy** klicken.
7. Einmalig in der Cloud-**Commands**-Konsole:
   ```
   php artisan bruno:import-legacy --snapshot
   php artisan bruno:admin <dein-name> --password=<vorlaeufiges-passwort>
   ```
   Danach im Admin unter **Konto** ein neues Passwort setzen.
8. **Domain**: Unter *Domains* die eigene Domain hinzufügen und die angezeigten DNS-Einträge beim Domain-Anbieter eintragen.

`database/snapshot/` enthält den Stand der alten Seite (Speisekarte, Einstellungen, Fotos – keine Passwörter) und wird
nur für den ersten Import gebraucht. Spätere Änderungen macht Bruno direkt im Admin der Live-Seite.

Updates: Änderungen in GitKraken committen und pushen – Laravel Cloud deployt automatisch.

## Produktion (IONOS Webhosting) – Alternative

Voraussetzung: Webhosting mit SSH, PHP ≥ 8.3 (Erweiterungen gd, intl, pdo_mysql, exif), MariaDB-Datenbank.

1. **Datenbank** im IONOS-Kundenbereich anlegen (MariaDB) – Host, Name, Benutzer, Passwort notieren.
2. **Code hochladen** (per Git oder SFTP) nach z. B. `~/brunos-imbiss` – ohne `vendor/`, `.env`, `public/uploads/`.
3. Per SSH:
   ```bash
   cd ~/brunos-imbiss
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
4. `.env` anpassen: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<domain>`, `LOG_LEVEL=warning`,
   Datenbank-Block auf `DB_CONNECTION=mariadb` mit den IONOS-Zugangsdaten umstellen.
5. Datenbank füllen – entweder frisch oder mit den lokalen Daten:
   ```bash
   php artisan migrate --force
   php artisan bruno:admin <name>
   ```
   Lokale Daten mitnehmen: lokal `public/uploads/` per SFTP hochladen und die Tabellen exportieren/importieren –
   oder auf dem Server `bruno:import-legacy` gegen eine Kopie der alten Datenbank laufen lassen.
6. `php artisan optimize`
7. **Domain** im IONOS-Kundenbereich auf das Verzeichnis `~/brunos-imbiss/public` zeigen lassen und SSL aktivieren.
   `public/.user.ini` setzt die Upload-Limits (20 MB pro Foto).

Updates später:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
```

## Aufbau

- `app/Actions/` – Fachlogik (Preise, Reihenfolge, Lieblinge, Öffnungsstatus, Datenübernahme)
- `app/Enums/` – Media-Collections/-Conversions, Kennzeichnungen, Wochentage, Foto-Plätze
- `app/Http/Controllers/Admin/` – Backend, `resources/views/admin/`
- `resources/views/pages/` – öffentliche Seiten, `public/assets/` – CSS/JS/Logo
- Alte URLs (`galerie.php`, `impressum.php`, …) leiten dauerhaft weiter.
