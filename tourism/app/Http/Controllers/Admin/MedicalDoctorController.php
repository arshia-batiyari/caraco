<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MedicalDoctorRequest;
use App\Models\MedicalDoctor;
use App\Models\MedicalProvider;
use App\Support\GallerySync;
use App\Support\ImageStore;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MedicalDoctorController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', MedicalDoctor::class);

        return view('admin.medical.doctors.index', [
            'records' => \App\Support\AdminList::paginate(MedicalDoctor::query()->with('translations'), request(), 'name'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MedicalDoctor::class);

        return view('admin.medical.doctors.form', $this->formData(new MedicalDoctor(['position' => 0, 'is_published' => false])));
    }

    public function store(MedicalDoctorRequest $request): RedirectResponse
    {
        $this->authorize('create', MedicalDoctor::class);
        $payload = $this->payload($request);
        $record = DB::transaction(function () use ($request, $payload) {
            $record = MedicalDoctor::query()->create($payload);
            LocaleText::sync($record, $request->input('translations', []), [
                'name', 'slug', 'specialty', 'biography', 'image_alt', 'meta_title', 'meta_description', 'canonical_url',
            ], 'name');

            return $record;
        });

        return redirect()->route('admin.medical.doctors.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(MedicalDoctor $doctor): View
    {
        $this->authorize('update', $doctor);

        return view('admin.medical.doctors.form', $this->formData($doctor->load('translations')));
    }

    public function update(MedicalDoctorRequest $request, MedicalDoctor $doctor): RedirectResponse
    {
        $this->authorize('update', $doctor);
        $payload = $this->payload($request, $doctor);
        DB::transaction(function () use ($request, $doctor, $payload) {
            $doctor->update($payload);
            LocaleText::sync($doctor, $request->input('translations', []), [
                'name', 'slug', 'specialty', 'biography', 'image_alt', 'meta_title', 'meta_description', 'canonical_url',
            ], 'name');
        });

        return redirect()->route('admin.medical.doctors.edit', $doctor)->with('status', __('admin.saved'));
    }

    public function destroy(MedicalDoctor $doctor): RedirectResponse
    {
        $this->authorize('delete', $doctor);
        $this->requireDeleteConfirmation();
        ImageStore::delete($doctor->image_path);
        $doctor->delete();

        return redirect()->route('admin.medical.doctors.index')->with('status', __('admin.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(MedicalDoctorRequest $request, ?MedicalDoctor $current = null): array
    {
        return [
            'medical_provider_id' => $request->input('medical_provider_id'),
            'is_published' => $request->boolean('is_published'),
            'position' => $request->integer('position'),
            'image_path' => GallerySync::replace($current?->image_path, $request->file('image'), $request->boolean('remove_image'), 'medical', 'image'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MedicalDoctor $record): array
    {
        return [
            'record' => $record,
            'providers' => MedicalProvider::query()->with('translations')->orderBy('position')->get(),
        ];
    }
}
