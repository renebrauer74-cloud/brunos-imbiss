<?php

namespace App\Models;

use App\Enums\HomePhotoSlotEnum;
use App\Enums\MediaCollectionEnum;
use App\Enums\MediaConversionEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Eines der drei Fotos unter der Speisekarte. Die Zeilen legt die Migration an.
 */
#[Fillable(['slot', 'caption'])]
class HomePhoto extends Model implements HasMedia
{
    use InteractsWithMedia;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'slot' => HomePhotoSlotEnum::class,
        ];
    }

    /**
     * @param  Builder<HomePhoto>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('slot');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollectionEnum::HomePhotos->value)
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
            ->fit(Fit::Crop, 600, 600)
            ->format('webp')
            ->quality(80)
            ->nonQueued();
    }
}
