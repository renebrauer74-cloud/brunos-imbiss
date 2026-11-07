<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Menu\MoveInOrderAction;
use App\Actions\Menu\SetFeaturedAction;
use App\Enums\MediaCollectionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FavoriteRequest;
use App\Http\Requests\Admin\PhotoRequest;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Unsere Lieblinge" auf der Startseite: Gerichte auswählen, sortieren, Fotos hochladen.
 * Das Foto hängt am Gericht und erscheint daher auch in der Speisekarte.
 */
class FavoriteController extends Controller
{
    public function index(): View
    {
        return view('admin.favorites', [
            'favorites' => MenuItem::query()->featured()->with(['category', 'media'])->get(),
            'categories' => Category::query()
                ->ordered()
                ->with(['menuItems' => fn (HasMany $query) => $query->where('featured', false)])
                ->get(),
        ]);
    }

    public function store(FavoriteRequest $request, SetFeaturedAction $setFeatured): RedirectResponse
    {
        $item = MenuItem::query()->findOrFail($request->integer('item_id'));
        $setFeatured->execute($item, true);

        return to_route('admin.favorites.index')->with('status', "„{$item->name}“ ist jetzt ein Liebling.");
    }

    public function destroy(MenuItem $menuItem, SetFeaturedAction $setFeatured): RedirectResponse
    {
        $setFeatured->execute($menuItem, false);

        return to_route('admin.favorites.index')->with('status', "„{$menuItem->name}“ wurde aus den Lieblingen entfernt.");
    }

    public function move(Request $request, MenuItem $menuItem, MoveInOrderAction $moveInOrder): RedirectResponse
    {
        $moveInOrder->execute(MenuItem::query()->featured()->get(), $menuItem, $request->integer('direction'), 'featured_order');

        return to_route('admin.favorites.index');
    }

    public function storePhoto(PhotoRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $menuItem->addMediaFromRequest('image')->toMediaCollection(MediaCollectionEnum::MenuItems->value);

        return to_route('admin.favorites.index')->with('status', "Foto für „{$menuItem->name}“ gespeichert.");
    }

    public function destroyPhoto(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->clearMediaCollection(MediaCollectionEnum::MenuItems->value);

        return to_route('admin.favorites.index')->with('status', "Foto von „{$menuItem->name}“ entfernt.");
    }
}
