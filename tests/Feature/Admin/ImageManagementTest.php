<?php

use App\Enums\MediaCollectionEnum;
use App\Models\GalleryImage;
use App\Models\HomePhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
});

it('shows the image administration', function () {
    actingAsAdmin()->get(route('admin.images.index'))
        ->assertOk()
        ->assertSee('Unter der Speisekarte')
        ->assertSee('Schnitzel satt');
});

it('replaces the photos and captions under the menu', function () {
    actingAsAdmin()
        ->put(route('admin.home-photos.update'), [
            'photos' => [
                1 => ['caption' => 'Unsere Currywurst', 'image' => UploadedFile::fake()->image('a.jpg', 800, 800)],
                2 => ['caption' => 'Pommes'],
                3 => ['caption' => ''],
            ],
        ])
        ->assertRedirect(route('admin.images.index').'#startseite');

    $photos = HomePhoto::query()->ordered()->get();
    $media = $photos[0]->getFirstMedia(MediaCollectionEnum::HomePhotos->value);

    expect($photos->pluck('caption')->all())->toBe(['Unsere Currywurst', 'Pommes', null])
        ->and($media->getPathRelativeToRoot())->toStartWith('home-photos/')
        ->and($photos[1]->hasMedia(MediaCollectionEnum::HomePhotos->value))->toBeFalse();

    actingAsAdmin()->put(route('admin.home-photos.update'), ['photos' => [1 => ['caption' => 'Weg damit', 'remove' => '1']]]);

    expect($photos[0]->fresh()->hasMedia(MediaCollectionEnum::HomePhotos->value))->toBeFalse();
    Storage::disk('uploads')->assertMissing($media->getPathRelativeToRoot());
});

it('uploads several gallery images at once', function () {
    GalleryImage::factory()->create(['sort_order' => 10]);

    actingAsAdmin()
        ->post(route('admin.gallery.store'), ['images' => [
            UploadedFile::fake()->image('eins.jpg'),
            UploadedFile::fake()->image('zwei.webp'),
        ]])
        ->assertRedirect(route('admin.images.index').'#galerie')
        ->assertSessionHas('status', '2 Bilder hochgeladen.');

    $uploaded = GalleryImage::query()->ordered()->get()->skip(1);

    expect($uploaded)->toHaveCount(2)
        ->and($uploaded->pluck('sort_order')->values()->all())->toBe([20, 30])
        ->and($uploaded->first()->getFirstMedia(MediaCollectionEnum::Gallery->value)->getPathRelativeToRoot())->toStartWith('gallery/');
});

it('requires at least one gallery image', function () {
    actingAsAdmin()
        ->post(route('admin.gallery.store'), [])
        ->assertSessionHasErrors(['images' => 'Bitte mindestens ein Bild auswählen.']);
});

it('saves captions, toggles, moves and deletes gallery images', function () {
    $first = GalleryImage::factory()->withImage()->create(['sort_order' => 10]);
    $second = GalleryImage::factory()->create(['sort_order' => 20]);
    $file = $first->getFirstMedia(MediaCollectionEnum::Gallery->value)->getPathRelativeToRoot();
    $admin = actingAsAdmin();

    $admin->put(route('admin.gallery.captions'), ['captions' => [$first->id => 'Lecker', $second->id => '']])->assertRedirect();
    expect($first->fresh()->caption)->toBe('Lecker')->and($second->fresh()->caption)->toBeNull();

    $admin->post(route('admin.gallery.toggle', $first))->assertRedirect();
    expect($first->fresh()->visible)->toBeFalse();

    $admin->post(route('admin.gallery.move', $second), ['direction' => -1])->assertRedirect();
    expect($second->fresh()->sort_order)->toBe(10);

    $admin->delete(route('admin.gallery.destroy', $first))->assertRedirect();
    $this->assertModelMissing($first);
    Storage::disk('uploads')->assertMissing($file);
});
