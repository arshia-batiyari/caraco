<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttractionCategoryRequest;
use App\Models\AttractionCategory;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttractionCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', AttractionCategory::class);

        return view('admin.attractions.categories.index', [
            'records' => AttractionCategory::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AttractionCategory::class);

        return view('admin.attractions.categories.form', [
            'record' => new AttractionCategory(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(AttractionCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', AttractionCategory::class);
        $record = AttractionCategory::query()->create([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['name', 'slug', 'summary'], 'name');

        return redirect()->route('admin.attraction-categories.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(AttractionCategory $category): View
    {
        $this->authorize('update', $category);

        return view('admin.attractions.categories.form', ['record' => $category->load('translations')]);
    }

    public function update(AttractionCategoryRequest $request, AttractionCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $category->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($category, $request->input('translations', []), ['name', 'slug', 'summary'], 'name');

        return redirect()->route('admin.attraction-categories.edit', $category)->with('status', __('admin.saved'));
    }

    public function destroy(AttractionCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $category->delete();

        return redirect()->route('admin.attraction-categories.index')->with('status', __('admin.saved'));
    }
}
