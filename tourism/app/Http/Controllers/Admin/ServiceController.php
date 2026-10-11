<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Service::class);

        return view('admin.services.index', [
            'records' => \App\Support\AdminList::paginate(Service::query()->with('translations'), request(), 'name'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('admin.services.form', ['record' => new Service(['type' => 'included', 'position' => 0])]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Service::class);
        $record = Service::query()->create([
            'type' => $request->input('type'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($record, $request->input('translations', []), ['name', 'description'], 'name');

        return redirect()->route('admin.services.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Service $service): View
    {
        $this->authorize('update', $service);

        return view('admin.services.form', ['record' => $service->load('translations')]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);
        $service->update([
            'type' => $request->input('type'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
        ]);
        LocaleText::sync($service, $request->input('translations', []), ['name', 'description'], 'name');

        return redirect()->route('admin.services.edit', $service)->with('status', __('admin.saved'));
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);
        $this->requireDeleteConfirmation();
        $service->delete();

        return redirect()->route('admin.services.index')->with('status', __('admin.saved'));
    }
}
