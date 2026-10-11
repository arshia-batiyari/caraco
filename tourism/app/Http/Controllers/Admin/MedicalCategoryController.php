<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalCategoryRequest;
use App\Models\MedicalCategory;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalCategory::class);

        return view('admin.medical.categories.index', [
            'records' => \App\Support\AdminList::paginate(MedicalCategory::query()->with('translations'), request(), 'name'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MedicalCategory::class);

        return view('admin.medical.categories.form', [
            'record' => new MedicalCategory(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(MedicalCategoryRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalCategory::class);
        $record = MedicalCategory::query()->create([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        $this->sync($request, $record);

        return redirect()->route('admin.medical.categories.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(MedicalCategory $category): View
    {
        $this->authorize('update', $category);

        return view('admin.medical.categories.form', [
            'record' => $category->load('translations'),
        ]);
    }

    public function update(MedicalCategoryRequest $request, MedicalCategory $category): RedirectResponse
    {
        $this->authorize('update', $category);
        $category->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        $this->sync($request, $category);

        return redirect()->route('admin.medical.categories.edit', $category)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalCategory $category): RedirectResponse
    {
        $this->authorize('delete', $category);
        $this->requireDeleteConfirmation();
        $category->delete();

        return redirect()->route('admin.medical.categories.index')->with('status', __('admin.saved'));
    }

    private function sync(MedicalCategoryRequest $request, MedicalCategory $record): void
    {
        LocaleText::sync($record, $request->input('translations', []), [
            'name', 'slug', 'summary', 'description', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');
    }
}
