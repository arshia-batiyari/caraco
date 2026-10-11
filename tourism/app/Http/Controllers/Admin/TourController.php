<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TourRequest;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\Service;
use App\Models\Tour;
use App\Models\TourImage;
use App\Models\TransportOption;
use App\Models\TravelStyle;
use App\Support\ImageStore;
use App\Support\TourRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Tour::class);

        return view('admin.tours.index', [
            'records' => \App\Support\AdminList::paginate(Tour::query()->with('translations'), request(), 'title'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Tour::class);

        return view('admin.tours.form', $this->formData(new Tour([
            'scope' => 'domestic',
            'position' => 0,
            'is_published' => false,
        ])));
    }

    public function store(TourRequest $request, TourRecorder $recorder): RedirectResponse
    {
        $this->authorize('create', Tour::class);

        $tour = $recorder->save($request, null);

        return redirect()->route('admin.tours.edit', $tour)->with('status', __('admin.saved'));
    }

    public function edit(Tour $tour): View
    {
        $this->authorize('update', $tour);

        return view('admin.tours.form', $this->formData($tour->load([
            'translations',
            'departures',
            'transports',
            'services',
            'stays.translations',
            'images',
            'days.translations',
            'routePoints.translations',
            'faqs.translations',
        ])));
    }

    public function update(TourRequest $request, Tour $tour, TourRecorder $recorder): RedirectResponse
    {
        $this->authorize('update', $tour);

        $recorder->save($request, $tour);

        return redirect()->route('admin.tours.edit', $tour)->with('status', __('admin.saved'));
    }

    public function destroy(Tour $tour): RedirectResponse
    {
        $this->authorize('delete', $tour);
        $this->requireDeleteConfirmation();
        $tour->images->each(fn (TourImage $image) => ImageStore::delete($image->path));
        ImageStore::delete($tour->cover_path);
        $tour->delete();

        return redirect()->route('admin.tours.index')->with('status', __('admin.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Tour $tour): array
    {
        return [
            'record' => $tour,
            'destinations' => Destination::query()->with('translations')->orderBy('position')->get(),
            'categories' => Category::query()->with('translations')->orderBy('position')->get(),
            'styles' => TravelStyle::query()->with('translations')->orderBy('position')->get(),
            'transports' => TransportOption::query()->with('translations')->orderBy('position')->get(),
            'services' => Service::query()->with('translations')->orderBy('type')->orderBy('position')->get(),
            'hotels' => Hotel::query()->with('translations')->orderBy('position')->get(),
        ];
    }
}
