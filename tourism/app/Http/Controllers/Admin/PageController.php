<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Page::class);

        return view('admin.pages.index', [
            'records' => \App\Support\AdminList::paginate(Page::query()->with('translations'), request(), 'title'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Page::class);

        return view('admin.pages.form', ['record' => new Page(['position' => 0])]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $this->authorize('create', Page::class);
        $record = DB::transaction(function () use ($request) {
            $record = Page::query()->create([
                'key' => LocaleText::blank($request->input('key')),
                'is_published' => $request->boolean('is_published'),
                'position' => $request->integer('position'),
            ]);
            LocaleText::sync($record, $request->input('translations', []), [
                'title', 'slug', 'body', 'meta_title', 'meta_description', 'canonical_url',
            ], 'title');

            return $record;
        });

        return redirect()->route('admin.pages.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);

        return view('admin.pages.form', ['record' => $page->load('translations')]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);
        DB::transaction(function () use ($request, $page) {
            $page->update([
                'key' => LocaleText::blank($request->input('key')),
                'is_published' => $request->boolean('is_published'),
                'position' => $request->integer('position'),
            ]);
            LocaleText::sync($page, $request->input('translations', []), [
                'title', 'slug', 'body', 'meta_title', 'meta_description', 'canonical_url',
            ], 'title');
        });

        return redirect()->route('admin.pages.edit', $page)->with('status', __('admin.saved'));
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);
        $this->requireDeleteConfirmation();
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', __('admin.saved'));
    }
}
