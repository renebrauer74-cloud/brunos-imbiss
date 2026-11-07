<?php

namespace App\Models;

use App\Enums\MediaCollectionEnum;
use App\Enums\MediaConversionEnum;
use Database\Factories\GalleryImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Bild der Bildergalerie.
 */
#[Fillable(['caption', 'visible', 'sort_order'])]
class GalleryImage extends Model implements HasMedia
{
    /** @use HasFactory<GalleryImageFactory> */
    use HasFactory, InteractsWithMedia;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<GalleryImage>  $query
     */
    #[Scope]
    protected function visible(Builder $query): void
    {
        $query->where('visible', true);
    }

    /**
     * @param  Builder<GalleryImage>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionEnum::Gallery->value)
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
