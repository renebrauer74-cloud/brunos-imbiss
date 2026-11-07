<?php

namespace App\Http\Controllers;

use App\Actions\Site\BuildRestaurantSchemaAction;
use App\Models\Category;
use App\Models\GalleryImage;
use App\Models\HomePhoto;
use App\Models\MenuItem;
use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(SiteSetting $settings, BuildRestaurantSchemaAction $buildSchema): View
    {
        $categories = Category::query()
            ->visible()
            ->ordered()
            ->with(['menuItems' => fn (HasMany $query) => $query->visible()->with('media')])
            ->get()
            ->filter(fn (Category $category): bool => $category->menuItems->isNotEmpty());

        return view('pages.home', [
            'favorites' => MenuItem::query()
                ->featured()
                ->visible()
                ->whereHas('category', fn (Builder $query) => $query->visible())
                ->with('media')
                ->get(),
            'categories' => $categories,
            'homePhotos' => HomePhoto::query()->ordered()->with('media')->get(),
            'galleryImageCount' => GalleryImage::query()->visible()->count(),
            'schema' => $buildSchema->execute($settings),
        ]);
    }
}
