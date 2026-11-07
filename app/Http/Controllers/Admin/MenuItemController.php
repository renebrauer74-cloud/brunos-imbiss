<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Menu\FormatPriceAction;
use App\Actions\Menu\MoveInOrderAction;
use App\Actions\Menu\ParsePriceAction;
use App\Actions\Menu\SaveMenuItemAction;
use App\Actions\Menu\SetFeaturedAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $categories = Category::query()->ordered()->get();

        if ($categories->isEmpty()) {
            return to_route('admin.categories.create')->with('error', 'Bitte zuerst eine Kategorie anlegen.');
        }

        $item = new MenuItem([
            'category_id' => $categories->firstWhere('id', $request->integer('category'))->id ?? $categories->first()->id,
            'visible' => true,
            'featured' => false,
            'tags' => [],
        ]);

        return view('admin.item', ['item' => $item, 'categories' => $categories, 'price' => '']);
    }

    public function store(MenuItemRequest $request, SaveMenuItemAction $saveMenuItem, ParsePriceAction $parsePrice): RedirectResponse
    {
        $item = $saveMenuItem->execute(new MenuItem, $this->itemData($request, $parsePrice), $request->file('image'));

        if ($request->boolean('save_new')) {
            return to_route('admin.items.create', ['category' => $item->category_id])
                ->with('status', "„{$item->name}“ wurde angelegt.");
        }

        return redirect()->to(route('admin.menu.index').'#cat-'.$item->category_id)
            ->with('status', "„{$item->name}“ wurde angelegt.");
    }

    public function edit(MenuItem $menuItem, FormatPriceAction $formatPrice): View
    {
        return view('admin.item', [
            'item' => $menuItem->load('media'),
            'categories' => Category::query()->ordered()->get(),
            'price' => $formatPrice->execute($menuItem->price_cents, withCurrency: false),
        ]);
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem, SaveMenuItemAction $saveMenuItem, ParsePriceAction $parsePrice): RedirectResponse
    {
        $saveMenuItem->execute(
            $menuItem,
            $this->itemData($request, $parsePrice),
            $request->file('image'),
            $request->boolean('remove_image'),
        );

        return redirect()->to(route('admin.menu.index').'#cat-'.$menuItem->category_id)
            ->with('status', "„{$menuItem->name}“ wurde gespeichert.");
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return redirect()->to(route('admin.menu.index').'#cat-'.$menuItem->category_id)
            ->with('status', "„{$menuItem->name}“ wurde gelöscht.");
    }

    public function toggle(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['visible' => ! $menuItem->visible]);

        return redirect()->to(route('admin.menu.index').'#cat-'.$menuItem->category_id)
            ->with('status', "„{$menuItem->name}“ ist jetzt ".($menuItem->visible ? 'sichtbar.' : 'ausgeblendet.'));
    }

    public function feature(MenuItem $menuItem, SetFeaturedAction $setFeatured): RedirectResponse
    {
        $setFeatured->execute($menuItem, ! $menuItem->featured);

        return redirect()->to(route('admin.menu.index').'#cat-'.$menuItem->category_id)
            ->with('status', "„{$menuItem->name}“ ".($menuItem->featured ? 'wird jetzt auf der Startseite empfohlen.' : 'ist keine Empfehlung mehr.'));
    }

    public function move(Request $request, MenuItem $menuItem, MoveInOrderAction $moveInOrder): RedirectResponse
    {
        $siblings = MenuItem::query()->where('category_id', $menuItem->category_id)->orderBy('sort_order')->orderBy('id')->get();
        $moveInOrder->execute($siblings, $menuItem, $request->integer('direction'));

        return redirect()->to(route('admin.menu.index').'#cat-'.$menuItem->category_id);
    }

    /**
     * @return array{category_id: int, name: string, description: ?string, price_cents: int, tags: list<string>, visible: bool, featured: bool}
     */
    private function itemData(MenuItemRequest $request, ParsePriceAction $parsePrice): array
    {
        return [
            'category_id' => $request->integer('category_id'),
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'price_cents' => (int) $parsePrice->execute($request->validated('price')),
            'tags' => array_values(array_unique($request->validated('tags', []))),
            'visible' => $request->boolean('visible'),
            'featured' => $request->boolean('featured'),
        ];
    }
}
