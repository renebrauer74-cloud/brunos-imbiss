<?php

namespace Database\Factories;

use App\Enums\MediaCollectionEnum;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(2, true),
            'description' => null,
            'price_cents' => fake()->numberBetween(5, 120) * 10,
            'tags' => [],
            'visible' => true,
            'sort_order' => fake()->numberBetween(1, 100) * 10,
            'featured' => false,
            'featured_order' => 0,
        ];
    }

    public function hidden(): static
    {
        return $this->state(['visible' => false]);
    }

    public function featured(int $order = 10): static
    {
        return $this->state(['featured' => true, 'featured_order' => $order]);
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (MenuItem $item): void {
            $item->addMedia(UploadedFile::fake()->image('gericht.jpg', 800, 600))
                ->toMediaCollection(MediaCollectionEnum::MenuItems->value);
        });
    }
}
