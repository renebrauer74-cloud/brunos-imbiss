<?php

namespace App\Models;

use App\Enums\MediaCollectionEnum;
use App\Enums\MediaConversionEnum;
use App\Enums\MenuItemTagEnum;
use Database\Factories\MenuItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Gericht oder Getränk der Speisekarte. Mit featured = true erscheint es unter "Unsere Lieblinge".
 */
#[Fillable(['category_id', 'name', 'description', 'price_cents', 'tags', 'visible', 'sort_order', 'featured', 'featured_order'])]
class MenuItem extends Model implements HasMedia
{
    /** @use HasFactory<MenuItemFactory> */
    use HasFactory, InteractsWithMedia;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'tags' => AsEnumCollection::of(MenuItemTagEnum::class),
            'visible' => 'boolean',
            'featured' => 'boolean',
            'sort_order' => 'integer',
            'featured_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('visible', true);
    }

    /**
     * Lieblinge in der Reihenfolge der Startseite.
     *
     * @param  Builder<MenuItem>  $query
     */
    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('featured', true)->orderBy('featured_order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionEnum::MenuItems->value)
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion(MediaConversionEnum::Web->value)
            ->fit(Fit::Max, 1600, 1600)
            ->format('webp')
            ->quality(82)
            ->nonQueued();

        $this->addMediaConversion(MediaConversionEnum::Thumb->value)
            ->fit(Fit::Crop, 400, 400)
            ->format('webp')
            ->quality(80)
            ->nonQueued();
    }
}
