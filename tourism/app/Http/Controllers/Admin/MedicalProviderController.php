<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalProviderRequest;
use App\Models\Destination;
use App\Models\MedicalProvider;
use App\Models\MedicalService;
use App\Support\GallerySync;
use App\Support\ImageStore;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicalProviderController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalProvider::class);

        return view('admin.medical.providers.index', [
            'records' => \App\Support\AdminList::paginate(MedicalProvider::query()->with('translations'), request(), 'name'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MedicalProvider::class);

        return view('admin.medical.providers.form', $this->formData(new MedicalProvider(['position' => 0, 'is_published' => false])));
    }

    public function store(MedicalProviderRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalProvider::class);
        $payload = $this->payload($request);
        $record = DB::transaction(function () use ($request, $payload) {
            $record = MedicalProvider::query()->create($payload);
            $this->sync($request, $record);

            return $record;
        });

        return redirect()->route('admin.medical.providers.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(MedicalProvider $provider): View
    {
        $this->authorize('update', $provider);

        return view('admin.medical.providers.form', $this->formData($provider->load(['translations', 'services'])));
    }

    public function update(MedicalProviderRequest $request, MedicalProvider $provider): RedirectResponse
    {
        $this->authorize('update', $provider);
        $payload = $this->payload($request, $provider);
        DB::transaction(function () use ($request, $provider, $payload) {
            $provider->update($payload);
            $this->sync($request, $provider);
        });

        return redirect()->route('admin.medical.providers.edit', $provider)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalProvider $provider): RedirectResponse
    {
        $this->authorize('delete', $provider);
        $this->requireDeleteConfirmation();
        ImageStore::delete($provider->cover_path);
        $provider->delete();

        return redirect()->route('admin.medical.providers.index')->with('status', __('admin.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(MedicalProviderRequest $request, ?MedicalProvider $current = null): array
    {
        return [
            'destination_id' => $request->input('destination_id'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
            'cover_path' => GallerySync::replace($current?->cover_path, $request->file('cover'), $request->boolean('remove_cover'), 'medical', 'cover'),
        ];
    }

    private function sync(MedicalProviderRequest $request, MedicalProvider $record): void
    {
        LocaleText::sync($record, $request->input('translations', []), [
            'name', 'slug', 'summary', 'description', 'cover_alt', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');
        $record->services()->sync($request->input('service_ids', []));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MedicalProvider $record): array
    {
        return [
            'record' => $record,
            'destinations' => Destination::query()->with('translations')->orderBy('position')->get(),
            'services' => MedicalService::query()->with('translations')->orderBy('position')->get(),
        ];
    }
}
