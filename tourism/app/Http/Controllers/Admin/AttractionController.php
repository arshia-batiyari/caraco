<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttractionRequest;
use App\Models\Attraction;
use App\Models\AttractionCategory;
use App\Models\Destination;
use App\Models\Tour;
use App\Support\GallerySync;
use App\Support\ImageStore;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttractionController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Attraction::class);

        return view('admin.attractions.index', [
            'records' => Attraction::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Attraction::class);

        return view('admin.attractions.form', $this->formData(new Attraction([
            'position' => 0,
            'is_published' => false,
            'unesco_designation' => 'none',
            'unesco_verified' => false,
        ])));
    }

    public function store(AttractionRequest $request): RedirectResponse
    {
        $this->authorize('create', Attraction::class);
        $record = Attraction::query()->create($this->payload($request));
        $this->sync($request, $record);

        return redirect()->route('admin.attractions.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Attraction $attraction): View
    {
        $this->authorize('update', $attraction);

        return view('admin.attractions.form', $this->formData($attraction->load(['translations', 'images.translations', 'tours'])));
    }

    public function update(AttractionRequest $request, Attraction $attraction): RedirectResponse
    {
        $this->authorize('update', $attraction);
        $attraction->update($this->payload($request, $attraction));
        $this->sync($request, $attraction);

        return redirect()->route('admin.attractions.edit', $attraction)->with('status', __('admin.saved'));
    }

    public function destroy(Attraction $attraction): RedirectResponse
    {
        $this->authorize('delete', $attraction);
        ImageStore::delete($attraction->cover_path);
        $attraction->load('images')->images->each(fn ($image) => ImageStore::delete($image->path));
        $attraction->delete();

        return redirect()->route('admin.attractions.index')->with('status', __('admin.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AttractionRequest $request, ?Attraction $current = null): array
    {
        $designation = $request->input('unesco_designation') ?: 'none';

        return [
            'destination_id' => $request->input('destination_id'),
            'attraction_category_id' => $request->input('attraction_category_id'),
            'classification' => $request->input('classification'),
            'unesco_designation' => $designation,
            'unesco_verified' => $designation === 'world_heritage' && $request->boolean('unesco_verified'),
            'inscribed_on' => $request->input('inscribed_on'),
            'unesco_reference' => LocaleText::blank($request->input('unesco_reference')),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'position' => $request->integer('position'),
            'cover_path' => GallerySync::replace($current?->cover_path, $request->file('cover'), $request->boolean('remove_cover'), 'attractions', 'cover'),
        ];
    }

    private function sync(AttractionRequest $request, Attraction $record): void
    {
        LocaleText::sync($record, $request->input('translations', []), [
            'title', 'slug', 'summary', 'description', 'visitor_info', 'cover_alt', 'meta_title', 'meta_description', 'canonical_url',
        ], 'title');
        $record->tours()->sync($request->input('tour_ids', []));
        GallerySync::images($request, $record, 'attractions');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Attraction $record): array
    {
        return [
            'record' => $record,
            'destinations' => Destination::query()->with('translations')->orderBy('position')->get(),
            'categories' => AttractionCategory::query()->with('translations')->orderBy('position')->get(),
            'tours' => Tour::query()->with('translations')->orderBy('position')->get(),
        ];
    }
}
