<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Menu\DeleteCategoryAction;
use App\Actions\Menu\MoveInOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function create(): View
    {
        return view('admin.category', ['category' => new Category(['visible' => true]), 'itemCount' => 0]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $category = Category::query()->create([
            ...$request->validated(),
            'visible' => $request->boolean('visible'),
            'sort_order' => (int) Category::query()->max('sort_order') + 10,
        ]);

        return redirect()->to(route('admin.menu.index').'#cat-'.$category->id)
            ->with('status', "Kategorie „{$category->name}“ angelegt.");
    }

    public function edit(Category $category): View
    {
        return view('admin.category', [
            'category' => $category,
            'itemCount' => $category->menuItems()->count(),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            ...$request->validated(),
            'visible' => $request->boolean('visible'),
        ]);

        return redirect()->to(route('admin.menu.index').'#cat-'.$category->id)
            ->with('status', "Kategorie „{$category->name}“ gespeichert.");
    }

    public function destroy(Category $category, DeleteCategoryAction $deleteCategory): RedirectResponse
    {
        $itemCount = $category->menuItems()->count();
        $deleteCategory->execute($category);

        return to_route('admin.menu.index')
            ->with('status', "Kategorie „{$category->name}“".($itemCount > 0 ? " und {$itemCount} Artikel" : '').' gelöscht.');
    }

    public function move(Request $request, Category $category, MoveInOrderAction $moveInOrder): RedirectResponse
    {
        $moveInOrder->execute(Category::query()->ordered()->get(), $category, $request->integer('direction'));

        return redirect()->to(route('admin.menu.index').'#cat-'.$category->id);
    }
}
