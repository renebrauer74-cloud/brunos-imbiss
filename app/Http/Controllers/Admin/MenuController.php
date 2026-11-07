<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Menu\UpdatePricesAction;
use App\Actions\Site\CheckOpeningHoursAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PricesRequest;
use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Übersicht der Speisekarte mit Schnell-Bearbeitung der Preise.
 */
class MenuController extends Controller
{
    public function index(SiteSetting $settings, CheckOpeningHoursAction $checkOpeningHours): View
    {
        $categories = Category::query()
            ->ordered()
            ->with(['menuItems' => fn (HasMany $query) => $query->with('media')])
            ->get();

        $items = $categories->flatMap->menuItems;

        $todo = array_keys(array_filter([
            'Adresse' => blank($settings->street),
            'Telefonnummer' => blank($settings->phone),
            'Öffnungszeiten' => ! $checkOpeningHours->execute($settings),
            'Impressum' => blank($settings->imprint),
        ]));

        return view('admin.menu', [
            'categories' => $categories,
            'total' => $items->count(),
            'hidden' => $items->where('visible', false)->count(),
            'withImage' => $items->filter(fn ($item) => $item->media->isNotEmpty())->count(),
            'todo' => $todo,
        ]);
    }

    public function updatePrices(PricesRequest $request, UpdatePricesAction $updatePrices): RedirectResponse
    {
        $result = $updatePrices->execute($request->validated('price'));
        $redirect = to_route('admin.menu.index')
            ->with('status', $result['changed'] === 1 ? '1 Preis aktualisiert.' : "{$result['changed']} Preise aktualisiert.");

        if ($result['invalid'] > 0) {
            $redirect->with('error', "{$result['invalid']} Preis(e) konnten nicht gelesen werden – bitte im Format 3,50 eingeben.");
        }

        return $redirect;
    }
}
