<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalServiceRequest;
use App\Models\MedicalCategory;
use App\Models\MedicalProvider;
use App\Models\MedicalService;
use App\Support\GallerySync;
use App\Support\ImageStore;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MedicalServiceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalService::class);

        return view('admin.medical.services.index', [
            'records' => MedicalService::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MedicalService::class);

        return view('admin.medical.services.form', $this->formData(new MedicalService(['position' => 0, 'is_published' => false])));
    }

    public function store(MedicalServiceRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalService::class);
        $record = MedicalService::query()->create($this->payload($request));
        $this->sync($request, $record);

        return redirect()->route('admin.medical.services.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(MedicalService $service): View
    {
        $this->authorize('update', $service);

        return view('admin.medical.services.form', $this->formData($service->load(['translations', 'images.translations', 'providers'])));
    }

    public function update(MedicalServiceRequest $request, MedicalService $service): RedirectResponse
    {
        $this->authorize('update', $service);
        $service->update($this->payload($request, $service));
        $this->sync($request, $service);

        return redirect()->route('admin.medical.services.edit', $service)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalService $service): RedirectResponse
    {
        $this->authorize('delete', $service);
        ImageStore::delete($service->cover_path);
        $service->images->each(function ($image) {
            ImageStore::delete($image->path);
        });
        $service->delete();

        return redirect()->route('admin.medical.services.index')->with('status', __('admin.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(MedicalServiceRequest $request, ?MedicalService $current = null): array
    {
        return [
            'medical_category_id' => $request->input('medical_category_id'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
            'cover_path' => GallerySync::replace($current?->cover_path, $request->file('cover'), $request->boolean('remove_cover'), 'medical', 'cover'),
        ];
    }

    private function sync(MedicalServiceRequest $request, MedicalService $record): void
    {
        LocaleText::sync($record, $request->input('translations', []), [
            'name', 'slug', 'summary', 'description', 'cover_alt', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');
        $record->providers()->sync($request->input('provider_ids', []));
        GallerySync::images($request, $record, 'medical');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MedicalService $record): array
    {
        return [
            'record' => $record,
            'categories' => MedicalCategory::query()->with('translations')->orderBy('position')->get(),
            'providers' => MedicalProvider::query()->with('translations')->orderBy('position')->get(),
        ];
    }
}
