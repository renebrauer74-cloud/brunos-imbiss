<?php

use App\Enums\MediaCollectionEnum;
use App\Enums\MenuItemTagEnum;
use App\Models\Category;
use App\Models\MenuItem;
use Database\Seeders\MenuSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('uploads');
});

it('shows the menu overview with open todos', function () {
    $this->seed(MenuSeeder::class);

    actingAsAdmin()->get(route('admin.menu.index'))
        ->assertOk()
        ->assertSee('55 Artikel · 3 ausgeblendet · 0 mit Bild')
        ->assertSee('Noch offen:')
        ->assertSee('Currywurst Pommes')
        ->assertSee('value="6,30"', false);
});

it('creates a menu item with photo and tags at the end of its category', function () {
    $category = Category::factory()->create();
    MenuItem::factory()->for($category)->create(['sort_order' => 40]);

    actingAsAdmin()
        ->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name' => 'Pommes Rot-Weiß',
            'description' => 'mit Ketchup und Mayo',
            'price' => '3,90 €',
            'tags' => ['vegetarisch', 'neu'],
            'visible' => '1',
            'featured' => '0',
            'image' => UploadedFile::fake()->image('pommes.jpg', 1200, 900),
        ])
        ->assertRedirect(route('admin.menu.index').'#cat-'.$category->id);

    $item = MenuItem::query()->where('name', 'Pommes Rot-Weiß')->sole();
    $media = $item->getFirstMedia(MediaCollectionEnum::MenuItems->value);

    expect($item->price_cents)->toBe(390)
        ->and($item->sort_order)->toBe(50)
        ->and($item->tags->all())->toBe([MenuItemTagEnum::Vegetarian, MenuItemTagEnum::New])
        ->and($media->getPathRelativeToRoot())->toStartWith('menu-items/');

    Storage::disk('uploads')->assertExists($media->getPathRelativeToRoot());
});

it('offers to create the next item in the same category', function () {
    $category = Category::factory()->create();

    actingAsAdmin()
        ->post(route('admin.items.store'), [
            'category_id' => $category->id,
            'name' => 'Cola',
            'price' => '2,20',
            'save_new' => '1',
        ])
        ->assertRedirect(route('admin.items.create', ['category' => $category->id]));
});

it('validates name and price', function () {
    $category = Category::factory()->create();

    actingAsAdmin()
        ->post(route('admin.items.store'), ['category_id' => $category->id, 'name' => '', 'price' => 'drei Euro'])
        ->assertSessionHasErrors(['name', 'price' => 'Bitte einen gültigen Preis eingeben, z. B. 3,50.']);

    expect(MenuItem::count())->toBe(0);
});

it('updates an item, moves it to another category and removes its photo', function () {
    $item = MenuItem::factory()->withImage()->create(['price_cents' => 350]);
    $file = $item->getFirstMedia(MediaCollectionEnum::MenuItems->value)->getPathRelativeToRoot();
    $other = Category::factory()->create();

    actingAsAdmin()
        ->put(route('admin.items.update', $item), [
            'category_id' => $other->id,
            'name' => 'Neuer Name',
            'price' => '4.5',
            'visible' => '0',
            'featured' => '1',
            'remove_image' => '1',
        ])
        ->assertRedirect();

    $item->refresh();

    expect($item->name)->toBe('Neuer Name')
        ->and($item->price_cents)->toBe(450)
        ->and($item->category_id)->toBe($other->id)
        ->and($item->visible)->toBeFalse()
        ->and($item->featured)->toBeTrue()
        ->and($item->hasMedia(MediaCollectionEnum::MenuItems->value))->toBeFalse();

    Storage::disk('uploads')->assertMissing($file);
});

it('deletes an item with its photo', function () {
    $item = MenuItem::factory()->withImage()->create();
    $file = $item->getFirstMedia(MediaCollectionEnum::MenuItems->value)->getPathRelativeToRoot();

    actingAsAdmin()->delete(route('admin.items.destroy', $item))->assertRedirect();

    $this->assertModelMissing($item);
    Storage::disk('uploads')->assertMissing($file);
});

it('saves changed prices in one go', function () {
    $a = MenuItem::factory()->create(['price_cents' => 300]);
    $b = MenuItem::factory()->create(['price_cents' => 500]);
    $c = MenuItem::factory()->create(['price_cents' => 700]);

    actingAsAdmin()
        ->put(route('admin.menu.prices'), ['price' => [$a->id => '3,20', $b->id => '5,00', $c->id => 'quatsch']])
        ->assertRedirect(route('admin.menu.index'))
        ->assertSessionHas('status', '1 Preis aktualisiert.')
        ->assertSessionHas('error');

    expect($a->fresh()->price_cents)->toBe(320)
        ->and($b->fresh()->price_cents)->toBe(500)
        ->and($c->fresh()->price_cents)->toBe(700);
});

it('toggles the visibility of an item', function () {
    $item = MenuItem::factory()->create();

    actingAsAdmin()->post(route('admin.items.toggle', $item))->assertRedirect();

    expect($item->fresh()->visible)->toBeFalse();
});

it('moves an item within its category', function () {
    $category = Category::factory()->create();
    $first = MenuItem::factory()->for($category)->create(['sort_order' => 10]);
    $second = MenuItem::factory()->for($category)->create(['sort_order' => 20]);

    actingAsAdmin()->post(route('admin.items.move', $second), ['direction' => -1])->assertRedirect();

    expect($second->fresh()->sort_order)->toBe(10)
        ->and($first->fresh()->sort_order)->toBe(20);
});

it('creates, edits, moves and deletes categories', function () {
    $admin = actingAsAdmin();
    $existing = Category::factory()->create(['sort_order' => 10]);

    $admin->post(route('admin.categories.store'), ['name' => 'Burger', 'visible' => '1'])->assertRedirect();
    $burger = Category::query()->where('name', 'Burger')->sole();
    expect($burger->sort_order)->toBe(20);

    $admin->put(route('admin.categories.update', $burger), ['name' => 'Burger & Co.', 'visible' => '0'])->assertRedirect();
    expect($burger->fresh())->name->toBe('Burger & Co.')->visible->toBeFalse();

    $admin->post(route('admin.categories.move', $burger), ['direction' => -1])->assertRedirect();
    expect($burger->fresh()->sort_order)->toBe(10)->and($existing->fresh()->sort_order)->toBe(20);

    $item = MenuItem::factory()->for($burger)->withImage()->create();
    $file = $item->getFirstMedia(MediaCollectionEnum::MenuItems->value)->getPathRelativeToRoot();

    $admin->delete(route('admin.categories.destroy', $burger))->assertRedirect(route('admin.menu.index'));

    $this->assertModelMissing($burger);
    $this->assertModelMissing($item);
    Storage::disk('uploads')->assertMissing($file);
});

it('renders the item and category forms', function () {
    $item = MenuItem::factory()->create(['price_cents' => 1050]);

    actingAsAdmin()->get(route('admin.items.create'))->assertOk()->assertSee('Neuer Artikel');
    actingAsAdmin()->get(route('admin.items.edit', $item))->assertOk()->assertSee('value="10,50"', false);
    actingAsAdmin()->get(route('admin.categories.create'))->assertOk()->assertSee('Neue Kategorie');
    actingAsAdmin()->get(route('admin.categories.edit', $item->category))->assertOk()->assertSee('Kategorie löschen');
});

it('asks for a category before the first item', function () {
    actingAsAdmin()->get(route('admin.items.create'))->assertRedirect(route('admin.categories.create'));
});
