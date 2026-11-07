<?php

namespace App\Actions\Menu;

use App\Models\Category;
use Illuminate\Support\Facades\DB;

/**
 * Löscht eine Kategorie mit allen Gerichten. Die Gerichte einzeln, damit die Media Library
 * ihre Fotos mit entfernt.
 */
class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        DB::transaction(function () use ($category): void {
            $category->menuItems->each->delete();
            $category->delete();
        });
    }
}
