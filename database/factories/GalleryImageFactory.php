<?php

namespace Database\Factories;

use App\Enums\MediaCollectionEnum;
use App\Models\GalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<GalleryImage>
 */
class GalleryImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'caption' => null,
            'visible' => true,
            'sort_order' => fake()->numberBetween(1, 100) * 10,
        ];
    }

    public function hidden(): static
    {
        return $this->state(['visible' => false]);
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (GalleryImage $image): void {
            $image->addMedia(UploadedFile::fake()->image('galerie.jpg', 800, 600))
                ->toMediaCollection(MediaCollectionEnum::Gallery->value);
        });
    }
}
