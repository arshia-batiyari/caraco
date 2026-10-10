<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Page::class);

        return view('admin.pages.index', [
            'pages' => Page::query()->orderBy('position')->orderBy('id')->with('translations')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Page::class);

        return view('admin.pages.form', [
            'page' => new Page(['position' => 0, 'is_published' => false]),
        ]);
    }

    public function store(PageRequest $request): RedirectResponse
    {
        $this->authorize('create', Page::class);
        $page = Page::query()->create([
            'position' => $request->integer('position'),
            'is_published' => $request->boolean('is_published'),
        ]);
        $this->sync($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('status', __('admin.saved'));
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);

        return view('admin.pages.form', [
            'page' => $page->load('translations'),
        ]);
    }

    public function update(PageRequest $request, Page $page): RedirectResponse
    {
        $this->authorize('update', $page);
        $page->update([
            'position' => $request->integer('position'),
            'is_published' => $request->boolean('is_published'),
        ]);
        $this->sync($request, $page);

        return redirect()->route('admin.pages.edit', $page)->with('status', __('admin.saved'));
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->authorize('delete', $page);
        $page->delete();

        return redirect()->route('admin.pages.index')->with('status', __('admin.saved'));
    }

    private function sync(PageRequest $request, Page $page): void
    {
        LocaleText::sync($page, $request->input('translations', []), [
            'title',
            'slug',
            'body',
            'meta_title',
            'meta_description',
            'canonical_url',
        ], 'title');
    }
}
