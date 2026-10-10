<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransportOptionRequest;
use App\Models\TransportOption;
use App\Support\CatalogCache;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransportOptionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', TransportOption::class);

        return view('admin.transports.index', [
            'records' => TransportOption::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TransportOption::class);

        return view('admin.transports.form', ['record' => new TransportOption(['mode' => 'airplane', 'position' => 0])]);
    }

    public function store(TransportOptionRequest $request): RedirectResponse
    {
        $this->authorize('create', TransportOption::class);
        $record = TransportOption::query()->create([
            'mode' => $request->input('mode'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['name', 'description'], 'name');
        CatalogCache::flush();

        return redirect()->route('admin.transports.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(TransportOption $transport): View
    {
        $this->authorize('update', $transport);

        return view('admin.transports.form', ['record' => $transport->load('translations')]);
    }

    public function update(TransportOptionRequest $request, TransportOption $transport): RedirectResponse
    {
        $this->authorize('update', $transport);
        $transport->update([
            'mode' => $request->input('mode'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($transport, $request->input('translations', []), ['name', 'description'], 'name');
        CatalogCache::flush();

        return redirect()->route('admin.transports.edit', $transport)->with('status', __('admin.saved'));
    }

    public function destroy(TransportOption $transport): RedirectResponse
    {
        $this->authorize('delete', $transport);
        $transport->delete();
        CatalogCache::flush();

        return redirect()->route('admin.transports.index')->with('status', __('admin.saved'));
    }
}
