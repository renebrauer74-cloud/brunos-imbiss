<?php

use App\Enums\MediaCollectionEnum;
use App\Enums\MenuItemTagEnum;
use App\Models\Category;
use App\Models\HomePhoto;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

it('creates an admin user with a prompted password', function () {
    $this->artisan('bruno:admin', ['username' => 'bruno'])
        ->expectsQuestion('Passwort', 'ein-sicheres-passwort')
        ->expectsOutput("Admin 'bruno' angelegt.")
        ->assertSuccessful();

    expect(Hash::check('ein-sicheres-passwort', User::sole()->password))->toBeTrue();
});

it('takes over an existing bcrypt hash', function () {
    $hash = Hash::make('altes-passwort');

    $this->artisan('bruno:admin', ['username' => 'bruno', '--password-hash' => $hash])->assertSuccessful();

    expect(Hash::check('altes-passwort', User::sole()->password))->toBeTrue();
});

it('accepts the password as option for consoles without input', function () {
    $this->artisan('bruno:admin', ['username' => 'bruno', '--password' => 'cloud-passwort'])->assertSuccessful();
    $this->artisan('bruno:admin', ['username' => 'kurz', '--password' => 'kurz'])->assertFailed();

    expect(Hash::check('cloud-passwort', User::sole()->password))->toBeTrue();
});

it('imports the bundled snapshot without passwords', function () {
    Storage::fake('uploads');

    $this->artisan('bruno:import-legacy', ['--snapshot' => true])
        ->expectsOutputToContain('6 Kategorien, 55 Artikel')
        ->expectsOutputToContain('bruno:admin')
        ->assertSuccessful();

    expect(MenuItem::query()->where('featured', true)->count())->toBe(4)
        ->and(HomePhoto::query()->get()->filter->hasMedia(MediaCollectionEnum::HomePhotos->value))->toHaveCount(3)
        ->and(User::count())->toBe(0);
});

it('refuses hashes that are not bcrypt', function () {
    $this->artisan('bruno:admin', ['username' => 'bruno', '--password-hash' => 'klartext'])->assertFailed();

    expect(User::count())->toBe(0);
});

describe('legacy import', function () {
    beforeEach(function () {
        Storage::fake('uploads');

        config(['database.connections.legacy' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => false]]);
        DB::purge('legacy');
        $legacy = Schema::connection('legacy');

        $legacy->create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('description')->default('');
            $table->integer('sort_order');
            $table->boolean('visible');
        });
        $legacy->create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('category_id');
            $table->string('name');
            $table->string('description')->default('');
            $table->integer('price_cents');
            $table->string('image')->nullable();
            $table->string('tags')->default('');
            $table->boolean('featured');
            $table->boolean('visible');
            $table->integer('sort_order');
            $table->timestamp('updated_at')->nullable();
            $table->integer('featured_order')->default(0);
        });
        $legacy->create('settings', function (Blueprint $table) {
            $table->string('name');
            $table->text('value');
        });
        $legacy->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username');
            $table->string('password_hash');
        });
        $legacy->create('gallery', function (Blueprint $table) {
            $table->increments('id');
            $table->string('image');
            $table->string('caption')->default('');
            $table->boolean('visible');
            $table->integer('sort_order');
            $table->timestamp('created_at')->nullable();
        });

        $this->uploads = sys_get_temp_dir().'/bruno-legacy-'.uniqid();
        mkdir($this->uploads);
        imagewebp(imagecreatetruecolor(40, 40), $this->uploads.'/wurst.webp');
        imagewebp(imagecreatetruecolor(40, 40), $this->uploads.'/foto1.webp');

        $db = DB::connection('legacy');
        $db->table('categories')->insert(['id' => 7, 'name' => 'Wurst', 'sort_order' => 10, 'visible' => 1]);
        $db->table('items')->insert([
            ['id' => 12, 'category_id' => 7, 'name' => 'Currywurst', 'price_cents' => 380, 'image' => 'wurst.webp', 'tags' => 'scharf,unbekannt', 'featured' => 1, 'visible' => 1, 'sort_order' => 10, 'featured_order' => 20],
            ['id' => 13, 'category_id' => 7, 'name' => 'Krakauer', 'price_cents' => 0, 'image' => 'fehlt.webp', 'tags' => '', 'featured' => 0, 'visible' => 0, 'sort_order' => 20, 'featured_order' => 0],
        ]);
        $db->table('settings')->insert([
            ['name' => 'site_title', 'value' => "Bruno's Imbiss"],
            ['name' => 'street', 'value' => 'Kortumstraße 1'],
            ['name' => 'phone', 'value' => ''],
            ['name' => 'hours', 'value' => json_encode(['mo' => ['closed' => false, 'from' => '11:00', 'to' => '20:00']])],
            ['name' => 'home_caption_1', 'value' => 'Alte Unterschrift'],
            ['name' => 'home_image_1', 'value' => 'foto1.webp'],
        ]);
        $db->table('users')->insert(['username' => 'bruno', 'password_hash' => Hash::make('altes-passwort')]);
    });

    afterEach(function () {
        array_map(unlink(...), glob($this->uploads.'/*'));
        rmdir($this->uploads);
    });

    it('imports menu, settings, photos and the admin login of the old version', function () {
        $this->artisan('bruno:import-legacy', ['--uploads' => $this->uploads])
            ->expectsOutputToContain('1 Kategorien, 2 Artikel')
            ->expectsOutput('Bild nicht gefunden: fehlt.webp')
            ->assertSuccessful();

        $item = MenuItem::query()->findOrFail(12);
        $settings = SiteSetting::query()->sole();
        $photo = HomePhoto::query()->where('slot', 1)->sole();

        expect(Category::query()->sole()->id)->toBe(7)
            ->and($item->tags->all())->toBe([MenuItemTagEnum::Spicy])
            ->and($item->featured)->toBeTrue()
            ->and($item->featured_order)->toBe(20)
            ->and($item->getFirstMedia(MediaCollectionEnum::MenuItems->value)->getPathRelativeToRoot())->toStartWith('menu-items/')
            ->and(MenuItem::query()->findOrFail(13)->visible)->toBeFalse()
            ->and($settings->street)->toBe('Kortumstraße 1')
            ->and($settings->phone)->toBeNull()
            ->and($settings->opening_hours['mo']['from'])->toBe('11:00')
            ->and($settings->opening_hours['so'])->toBe(['closed' => false, 'from' => '', 'to' => ''])
            ->and($photo->caption)->toBe('Alte Unterschrift')
            ->and($photo->hasMedia(MediaCollectionEnum::HomePhotos->value))->toBeTrue()
            ->and(Hash::check('altes-passwort', User::query()->where('username', 'bruno')->sole()->password))->toBeTrue()
            ->and(is_file($this->uploads.'/wurst.webp'))->toBeTrue();
    });

    it('refuses to import into a menu that already contains data', function () {
        Category::factory()->create();

        $this->artisan('bruno:import-legacy', ['--uploads' => $this->uploads])->assertFailed();

        expect(MenuItem::count())->toBe(0);
    });
});
