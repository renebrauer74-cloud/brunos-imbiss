<?php

use App\Enums\MediaCollectionEnum;
use App\Enums\MediaConversionEnum;
use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use Database\Seeders\MenuSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
});

it('shows the seeded menu on the home page', function () {
    $this->seed(MenuSeeder::class);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Speisekarte')
        ->assertSee('Wurst &amp; Frikadellen', false)
        ->assertSee('Currywurst Pommes')
        ->assertSee('6,30 €')
        ->assertSee('Unsere Lieblinge')
        ->assertDontSee('Krakauer') // ohne Preis ausgeblendet
        ->assertSee('FastFoodRestaurant');
});

it('hides invisible items and categories', function () {
    $category = Category::factory()->create(['name' => 'Burger']);
    MenuItem::factory()->for($category)->create(['name' => 'Sichtbarer Burger']);
    MenuItem::factory()->for($category)->hidden()->create(['name' => 'Geheimer Burger']);
    $hiddenCategory = Category::factory()->hidden()->create(['name' => 'Desserts']);
    MenuItem::factory()->for($hiddenCategory)->featured()->create(['name' => 'Eis']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sichtbarer Burger')
        ->assertDontSee('Geheimer Burger')
        ->assertDontSee('Desserts')
        ->assertDontSee('Unsere Lieblinge');
});

it('shows the photo of a favorite instead of its number', function () {
    $item = MenuItem::factory()->featured()->withImage()->create(['name' => 'Ruhr Pott']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Unsere Lieblinge')
        ->assertSee($item->getFirstMediaUrl(MediaCollectionEnum::MenuItems->value, MediaConversionEnum::Web->value))
        ->assertDontSee('fav-number');
});

it('shows the home photo placeholders and their default captions', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Appetit bekommen?')
        ->assertSee('Frisch aus der Fritteuse')
        ->assertSee('polaroid-placeholder');
});

it('links the gallery only when it has visible images', function () {
    $this->get(route('home'))->assertDontSee(route('gallery'));

    GalleryImage::factory()->withImage()->create(['caption' => 'Frisch gezapft']);

    $this->get(route('home'))->assertSee(route('gallery'))->assertSee('Zur Bildergalerie (1)');
    $this->get(route('gallery'))->assertOk()->assertSee('Frisch gezapft');
});

it('does not show hidden gallery images', function () {
    GalleryImage::factory()->hidden()->withImage()->create(['caption' => 'Versteckt']);

    $this->get(route('gallery'))->assertOk()->assertDontSee('Versteckt')->assertSee('Hier gibt’s bald leckere Bilder');
});

it('uses the contact data from the settings', function () {
    app(SiteSetting::class)->update([
        'street' => 'Kortumstraße 1',
        'zip_city' => '44787 Bochum',
        'phone' => '0234 123456',
        'notice' => 'Heute Wildschwein-Bratwurst!',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Kortumstraße 1')
        ->assertSee('tel:+49234123456')
        ->assertSee('Heute Wildschwein-Bratwurst!');
});

it('shows the legal pages', function () {
    $this->get(route('imprint'))->assertOk()->assertSee('Impressum')->assertSee('noindex');
    $this->get(route('privacy'))->assertOk()->assertSee('Datenschutzerklärung');

    app(SiteSetting::class)->update(['imprint' => "Inhaber: Bruno\nBochum"]);

    $this->get(route('imprint'))->assertSee('Inhaber: Bruno<br>', false);
});

it('redirects the urls of the old version permanently', function (string $old, string $new) {
    $this->get($old)->assertStatus(301)->assertRedirect($new);
})->with([
    ['/galerie.php', '/galerie'],
    ['/impressum.php', '/impressum'],
    ['/datenschutz.php', '/datenschutz'],
    ['/admin/login.php', '/admin/login'],
]);
