<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HotelRequest;
use App\Models\Destination;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Support\CatalogCache;
use App\Support\ImageStore;
use App\Support\LocaleText;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;
use RuntimeException;

class HotelController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Hotel::class);

        return view('admin.hotels.index', [
            'records' => \App\Support\AdminList::paginate(Hotel::query()->with('translations'), request(), 'name'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Hotel::class);

        return view('admin.hotels.form', [
            'record' => new Hotel(['position' => 0]),
            'destinations' => Destination::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function store(HotelRequest $request): RedirectResponse
    {
        $this->authorize('create', Hotel::class);
        $record = DB::transaction(function () use ($request) {
            $record = Hotel::query()->create($this->payload($request));
            $this->sync($request, $record);

            return $record;
        });

        return redirect()->route('admin.hotels.edit', $record)->with('status', __('admin.saved'));
    }

    public function edit(Hotel $hotel): View
    {
        $this->authorize('update', $hotel);

        return view('admin.hotels.form', [
            'record' => $hotel->load('translations', 'facilities.translations', 'images'),
            'destinations' => Destination::query()->with('translations')->orderBy('position')->get(),
        ]);
    }

    public function update(HotelRequest $request, Hotel $hotel): RedirectResponse
    {
        $this->authorize('update', $hotel);
        DB::transaction(function () use ($request, $hotel) {
            $hotel->update($this->payload($request));
            $this->sync($request, $hotel);
        });

        return redirect()->route('admin.hotels.edit', $hotel)->with('status', __('admin.saved'));
    }

    public function destroy(Hotel $hotel): RedirectResponse
    {
        $this->authorize('delete', $hotel);
        $this->requireDeleteConfirmation();
        $hotel->images->each(fn (HotelImage $image) => ImageStore::delete($image->path));
        $hotel->delete();
        CatalogCache::flush();

        return redirect()->route('admin.hotels.index')->with('status', __('admin.saved'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(HotelRequest $request): array
    {
        return [
            'destination_id' => $request->input('destination_id') ?: null,
            'accommodation_category' => LocaleText::blank($request->input('accommodation_category')),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'position' => $request->integer('position'),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'website' => LocaleText::blank($request->input('website')),
        ];
    }

    private function sync(HotelRequest $request, Hotel $hotel): void
    {
        LocaleText::sync($hotel, $request->input('translations', []), [
            'name', 'slug', 'summary', 'description', 'address', 'room_details', 'meta_title', 'meta_description', 'canonical_url',
        ], 'name');

        $hotel->facilities()->delete();
        $position = 0;
        foreach ($request->input('facilities', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $translations = is_array($row['translations'] ?? null) ? $row['translations'] : [];
            $filled = false;
            foreach ($translations as $localeRow) {
                if (is_array($localeRow) && filled($localeRow['name'] ?? null)) {
                    $filled = true;
                }
            }
            if (! $filled) {
                continue;
            }
            $facility = $hotel->facilities()->create(['position' => $position]);
            $position++;
            LocaleText::sync($facility, $translations, ['name'], 'name');
        }

        $remove = array_map('intval', (array) $request->input('remove_images', []));
        $hotel->images()->whereIn('id', $remove)->get()->each(function (HotelImage $image) {
            ImageStore::delete($image->path);
            $image->delete();
        });

        try {
            $position = (int) $hotel->images()->max('position') + 1;
            foreach ($request->file('images', []) as $file) {
                if (! $file instanceof UploadedFile || ! $file->isValid()) {
                    continue;
                }
                $hotel->images()->create([
                    'path' => ImageStore::store($file, 'hotels'),
                    'position' => $position,
                ]);
                $position++;
            }
        } catch (RuntimeException) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'images' => __('admin.validation.image'),
            ]);
        }

        CatalogCache::flush();
    }
}
