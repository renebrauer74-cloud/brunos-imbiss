<?php

namespace App\Actions\Legacy;

use App\Enums\HomePhotoSlotEnum;
use App\Enums\MediaCollectionEnum;
use App\Enums\MenuItemTagEnum;
use App\Enums\WeekdayEnum;
use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\HomePhoto;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\HasMedia;

/**
 * Übernimmt Speisekarte, Einstellungen, Fotos, Galerie und Admin-Zugang der alten Plain-PHP-Version.
 * Die IDs bleiben erhalten, Bilder werden aus dem alten uploads-Ordner kopiert (das Original bleibt liegen).
 */
class ImportLegacyDataAction
{
    /** Einstellungen, die 1:1 in eine Spalte von site_settings wandern */
    private const SETTING_COLUMNS = [
        'site_title', 'tagline', 'slogan', 'notice', 'street', 'zip_city', 'phone', 'email',
        'hours_note', 'about', 'imprint', 'privacy',
    ];

    /** @var list<string> */
    private array $missingImages = [];

    /**
     * @return array{categories: int, items: int, images: int, gallery: int, users: int, missing_images: list<string>}
     */
    public function execute(ConnectionInterface $legacy, ?string $uploadsPath): array
    {
        $this->missingImages = [];
        $uploadsPath = $uploadsPath !== null ? rtrim($uploadsPath, '/\\') : null;
        $images = 0;

        DB::transaction(function () use ($legacy): void {
            foreach ($legacy->table('categories')->orderBy('id')->get() as $row) {
                $category = new Category([
                    'name' => $row->name,
                    'description' => $this->nullable($row->description),
                    'sort_order' => (int) $row->sort_order,
                    'visible' => (bool) $row->visible,
                ]);
                $category->id = (int) $row->id;
                $category->save();
            }

            foreach ($legacy->table('items')->orderBy('id')->get() as $row) {
                $item = new MenuItem([
                    'category_id' => (int) $row->category_id,
                    'name' => $row->name,
                    'description' => $this->nullable($row->description),
                    'price_cents' => (int) $row->price_cents,
                    'tags' => $this->tags((string) $row->tags),
                    'visible' => (bool) $row->visible,
                    'sort_order' => (int) $row->sort_order,
                    'featured' => (bool) $row->featured,
                    'featured_order' => (int) ($row->featured_order ?? 0),
                ]);
                $item->id = (int) $row->id;
                $item->created_at = $row->updated_at ?? now();
                $item->updated_at = $row->updated_at ?? now();
                $item->save();
            }

            $settings = $legacy->table('settings')->pluck('value', 'name');

            SiteSetting::query()->firstOrCreate([], ['site_title' => "Bruno's Imbiss"])->update([
                ...collect(self::SETTING_COLUMNS)
                    ->mapWithKeys(fn (string $column): array => [$column => $this->nullable($settings[$column] ?? null)])
                    ->put('site_title', $this->nullable($settings['site_title'] ?? null) ?? "Bruno's Imbiss")
                    ->all(),
                'opening_hours' => $this->hours($settings['hours'] ?? null),
            ]);

            foreach (HomePhotoSlotEnum::cases() as $slot) {
                HomePhoto::query()->updateOrCreate(
                    ['slot' => $slot->value],
                    ['caption' => $this->nullable($settings['home_caption_'.$slot->value] ?? $slot->defaultCaption())],
                );
            }

            foreach ($legacy->table('gallery')->orderBy('id')->get() as $row) {
                $image = new GalleryImage([
                    'caption' => $this->nullable($row->caption),
                    'visible' => (bool) $row->visible,
                    'sort_order' => (int) $row->sort_order,
                ]);
                $image->id = (int) $row->id;
                $image->created_at = $row->created_at ?? now();
                $image->save();
            }

            foreach ($legacy->table('users')->orderBy('id')->get() as $row) {
                // bcrypt-Hash aus password_hash() – Laravel prüft ihn direkt, kein neues Passwort nötig
                $user = User::query()->firstOrNew(['username' => $row->username]);
                $user->password = $row->password_hash;
                $user->save();
            }
        });

        // Postgres zählt IDs per Sequenz – nach dem Einfügen mit festen IDs nachziehen
        if (DB::connection()->getDriverName() === 'pgsql') {
            foreach (['categories', 'menu_items', 'gallery_images'] as $table) {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 0) + 1, false)");
            }
        }

        // Bilder nach dem Speichern der Datensätze kopieren (Dateien lassen sich nicht zurückrollen)
        if ($uploadsPath !== null) {
            foreach ($legacy->table('items')->whereNotNull('image')->where('image', '<>', '')->get(['id', 'image']) as $row) {
                $images += $this->attach(MenuItem::query()->find($row->id), $uploadsPath, $row->image, MediaCollectionEnum::MenuItems);
            }

            foreach ($legacy->table('gallery')->get(['id', 'image']) as $row) {
                $images += $this->attach(GalleryImage::query()->find($row->id), $uploadsPath, $row->image, MediaCollectionEnum::Gallery);
            }

            $settings = $legacy->table('settings')->pluck('value', 'name');
            foreach (HomePhoto::query()->ordered()->get() as $photo) {
                $images += $this->attach($photo, $uploadsPath, $settings['home_image_'.$photo->slot->value] ?? null, MediaCollectionEnum::HomePhotos);
            }
        }

        return [
            'categories' => Category::query()->count(),
            'items' => MenuItem::query()->count(),
            'images' => $images,
            'gallery' => GalleryImage::query()->count(),
            'users' => User::query()->count(),
            'missing_images' => $this->missingImages,
        ];
    }

    private function attach(?HasMedia $model, string $uploadsPath, ?string $file, MediaCollectionEnum $collection): int
    {
        if ($model === null || blank($file)) {
            return 0;
        }

        $path = $uploadsPath.DIRECTORY_SEPARATOR.basename($file);

        if (! is_file($path)) {
            $this->missingImages[] = basename($file);

            return 0;
        }

        $model->addMedia($path)->preservingOriginal()->toMediaCollection($collection->value);

        return 1;
    }

    /**
     * @return list<string>
     */
    private function tags(string $tags): array
    {
        return collect(explode(',', $tags))
            ->map(fn (string $tag): ?MenuItemTagEnum => MenuItemTagEnum::tryFrom(trim($tag)))
            ->filter()
            ->map(fn (MenuItemTagEnum $tag): string => $tag->value)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @return array<string, array{closed: bool, from: string, to: string}>
     */
    private function hours(?string $json): array
    {
        $stored = json_decode((string) $json, true);
        $hours = [];

        foreach (WeekdayEnum::cases() as $day) {
            $entry = is_array($stored[$day->value] ?? null) ? $stored[$day->value] : [];
            $hours[$day->value] = [
                'closed' => (bool) ($entry['closed'] ?? false),
                'from' => (string) ($entry['from'] ?? ''),
                'to' => (string) ($entry['to'] ?? ''),
            ];
        }

        return $hours;
    }

    private function nullable(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
