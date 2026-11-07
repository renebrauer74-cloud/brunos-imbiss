<?php

namespace App\Actions\Menu;

use App\Enums\MediaCollectionEnum;
use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Legt ein Gericht an oder speichert es – inklusive Reihenfolge, Liebling-Status und Foto.
 */
class SaveMenuItemAction
{
    public function __construct(private SetFeaturedAction $setFeatured) {}

    /**
     * @param  array{category_id: int, name: string, description: ?string, price_cents: int, tags: list<string>, visible: bool, featured: bool}  $data
     */
    public function execute(MenuItem $item, array $data, ?UploadedFile $image = null, bool $removeImage = false): MenuItem
    {
        DB::transaction(function () use ($item, $data): void {
            $categoryChanged = $item->category_id !== $data['category_id'];

            $item->fill([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price_cents' => $data['price_cents'],
                'tags' => $data['tags'],
                'visible' => $data['visible'],
            ]);

            // Neue oder umgezogene Gerichte kommen ans Ende ihrer Kategorie
            if ($categoryChanged) {
                $item->sort_order = (int) MenuItem::query()->where('category_id', $data['category_id'])->max('sort_order') + 10;
            }

            $item->save();

            $this->setFeatured->execute($item, $data['featured']);
        });

        if ($image !== null) {
            $item->addMedia($image)->toMediaCollection(MediaCollectionEnum::MenuItems->value);
        } elseif ($removeImage) {
            $item->clearMediaCollection(MediaCollectionEnum::MenuItems->value);
        }

        return $item;
    }
}
