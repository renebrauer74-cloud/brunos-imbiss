<?php

use App\Enums\MediaCollectionEnum;
use App\Models\MenuItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
});

it('lists the favorites and the dishes that can be added', function () {
    MenuItem::factory()->featured()->create(['name' => 'Ruhr Pott']);
    MenuItem::factory()->create(['name' => 'Hot Dog']);

    actingAsAdmin()->get(route('admin.favorites.index'))
        ->assertOk()
        ->assertSee('Ruhr Pott')
        ->assertSee('<option value', false)
        ->assertSee('Hot Dog');
});

it('adds a favorite at the end and removes it again', function () {
    MenuItem::factory()->featured(10)->create();
    $item = MenuItem::factory()->create();

    actingAsAdmin()->post(route('admin.favorites.store'), ['item_id' => $item->id])->assertRedirect(route('admin.favorites.index'));
    expect($item->fresh())->featured->toBeTrue()->featured_order->toBe(20);

    actingAsAdmin()->delete(route('admin.favorites.destroy', $item))->assertRedirect(route('admin.favorites.index'));
    expect($item->fresh()->featured)->toBeFalse();
});

it('changes the order of the favorites', function () {
    $first = MenuItem::factory()->featured(10)->create();
    $second = MenuItem::factory()->featured(20)->create();

    actingAsAdmin()->post(route('admin.favorites.move', $first), ['direction' => 1])->assertRedirect();

    expect(MenuItem::query()->featured()->pluck('id')->all())->toBe([$second->id, $first->id]);
});

it('uploads and deletes the photo of a favorite', function () {
    $item = MenuItem::factory()->featured()->create();

    actingAsAdmin()
        ->post(route('admin.favorites.photo.store', $item), ['image' => UploadedFile::fake()->image('wurst.png', 900, 900)])
        ->assertRedirect(route('admin.favorites.index'));

    $file = $item->fresh()->getFirstMedia(MediaCollectionEnum::MenuItems->value)->getPathRelativeToRoot();
    Storage::disk('uploads')->assertExists($file);

    actingAsAdmin()->delete(route('admin.favorites.photo.destroy', $item))->assertRedirect();

    expect($item->fresh()->hasMedia(MediaCollectionEnum::MenuItems->value))->toBeFalse();
    Storage::disk('uploads')->assertMissing($file);
});

it('rejects files that are not images', function () {
    $item = MenuItem::factory()->featured()->create();

    actingAsAdmin()
        ->post(route('admin.favorites.photo.store', $item), ['image' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors('image');
});
