<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Öffentliche Seiten
|--------------------------------------------------------------------------
*/

Route::get('/', HomeController::class)->name('home');
Route::get('galerie', GalleryController::class)->name('gallery');
Route::view('impressum', 'pages.impressum')->name('imprint');
Route::view('datenschutz', 'pages.privacy')->name('privacy');

/*
|--------------------------------------------------------------------------
| Admin-Bereich
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

        // Speisekarte
        Route::get('/', [Admin\MenuController::class, 'index'])->name('menu.index');
        Route::put('prices', [Admin\MenuController::class, 'updatePrices'])->name('menu.prices');

        Route::resource('categories', Admin\CategoryController::class)->except(['index', 'show']);
        Route::post('categories/{category}/move', [Admin\CategoryController::class, 'move'])->name('categories.move');

        Route::resource('items', Admin\MenuItemController::class)->except(['index', 'show'])
            ->parameters(['items' => 'menuItem']);
        Route::post('items/{menuItem}/toggle', [Admin\MenuItemController::class, 'toggle'])->name('items.toggle');
        Route::post('items/{menuItem}/feature', [Admin\MenuItemController::class, 'feature'])->name('items.feature');
        Route::post('items/{menuItem}/move', [Admin\MenuItemController::class, 'move'])->name('items.move');

        // Unsere Lieblinge
        Route::get('favorites', [Admin\FavoriteController::class, 'index'])->name('favorites.index');
        Route::post('favorites', [Admin\FavoriteController::class, 'store'])->name('favorites.store');
        Route::delete('favorites/{menuItem}', [Admin\FavoriteController::class, 'destroy'])->name('favorites.destroy');
        Route::post('favorites/{menuItem}/move', [Admin\FavoriteController::class, 'move'])->name('favorites.move');
        Route::post('favorites/{menuItem}/photo', [Admin\FavoriteController::class, 'storePhoto'])->name('favorites.photo.store');
        Route::delete('favorites/{menuItem}/photo', [Admin\FavoriteController::class, 'destroyPhoto'])->name('favorites.photo.destroy');

        // Bilder: Startseiten-Fotos und Galerie
        Route::get('images', [Admin\ImageController::class, 'index'])->name('images.index');
        Route::put('home-photos', [Admin\HomePhotoController::class, 'update'])->name('home-photos.update');
        Route::post('gallery', [Admin\GalleryImageController::class, 'store'])->name('gallery.store');
        Route::put('gallery/captions', [Admin\GalleryImageController::class, 'updateCaptions'])->name('gallery.captions');
        Route::post('gallery/{galleryImage}/toggle', [Admin\GalleryImageController::class, 'toggle'])->name('gallery.toggle');
        Route::post('gallery/{galleryImage}/move', [Admin\GalleryImageController::class, 'move'])->name('gallery.move');
        Route::delete('gallery/{galleryImage}', [Admin\GalleryImageController::class, 'destroy'])->name('gallery.destroy');

        // Einstellungen und Konto
        Route::get('settings', [Admin\SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::get('account', [Admin\AccountController::class, 'edit'])->name('account.edit');
        Route::put('account', [Admin\AccountController::class, 'update'])->name('account.update');
    });
});

/*
|--------------------------------------------------------------------------
| Alte URLs der Plain-PHP-Version (dauerhaft weiterleiten)
|--------------------------------------------------------------------------
*/

Route::permanentRedirect('galerie.php', 'galerie');
Route::permanentRedirect('impressum.php', 'impressum');
Route::permanentRedirect('datenschutz.php', 'datenschutz');
Route::permanentRedirect('admin/index.php', 'admin');
Route::permanentRedirect('admin/login.php', 'admin/login');
