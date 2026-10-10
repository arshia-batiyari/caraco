<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\CatalogCache;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.categories.index', [
            'records' => Category::query()->with('translations')->orderBy('position')->orderBy('id')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.form', [
            'record' => new Category(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', Category::class);
        $record = Category::query()->create([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), [
            'name', 'slug', 'summary', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');
        CatalogCache::flush();

        return redirect()->route('admin.categories.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.form', [
            'record' => $category->load('translations'),
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $category->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($category, $request->input('translations', []), [
            'name', 'slug', 'summary', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');
        CatalogCache::flush();

        return redirect()->route('admin.categories.edit', $category)->with('status', __('admin.saved'));
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $category->delete();
        CatalogCache::flush();

        return redirect()->route('admin.categories.index')->with('status', __('admin.saved'));
    }
}
