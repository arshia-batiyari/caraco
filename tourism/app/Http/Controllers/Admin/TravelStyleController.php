<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TravelStyleRequest;
use App\Models\TravelStyle;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TravelStyleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TravelStyle::class);

        return view('admin.styles.index', [
            'records' => TravelStyle::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TravelStyle::class);

        return view('admin.styles.form', ['record' => new TravelStyle(['position' => 0])]);
    }

    public function store(TravelStyleRequest $request): RedirectResponse
    {
        $this->authorize('create', TravelStyle::class);
        $record = TravelStyle::query()->create([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['name', 'slug', 'summary'], 'name');

        return redirect()->route('admin.styles.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(TravelStyle $style): View
    {
        $this->authorize('update', $style);

        return view('admin.styles.form', ['record' => $style->load('translations')]);
    }

    public function update(TravelStyleRequest $request, TravelStyle $style): RedirectResponse
    {
        $this->authorize('update', $style);
        $style->update([
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($style, $request->input('translations', []), ['name', 'slug', 'summary'], 'name');

        return redirect()->route('admin.styles.edit', $style)->with('status', __('admin.saved'));
    }

    public function destroy(TravelStyle $style): RedirectResponse
    {
        $this->authorize('delete', $style);
        $style->delete();

        return redirect()->route('admin.styles.index')->with('status', __('admin.saved'));
    }
}
