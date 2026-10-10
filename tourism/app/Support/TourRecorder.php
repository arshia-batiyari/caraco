<?php

namespace App\Support;

use App\Models\Faq;
use App\Models\ItineraryDay;
use App\Models\RoutePoint;
use App\Models\Tour;
use App\Models\TourHotel;
use App\Models\TourImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class TourRecorder
{
    public function save(Request $request, ?Tour $tour): Tour
    {
        $images = [];
        foreach ($request->file('images', []) as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid() || $file->getSize() === 0) {
                continue;
            }
            try {
                $images[] = ImageStore::store($file, 'tours');
            } catch (RuntimeException) {
                throw ValidationException::withMessages([
                    'images' => __('admin.validation.image'),
                ]);
            }
        }

        try {
            return DB::transaction(function () use ($request, $tour, $images) {
                $payload = [
                    'scope' => $request->input('scope'),
                    'destination_id' => $request->input('destination_id') ?: null,
                    'category_id' => $request->input('category_id') ?: null,
                    'travel_style_id' => $request->input('travel_style_id') ?: null,
                    'is_published' => $request->boolean('is_published'),
                    'is_featured' => $request->boolean('is_featured'),
                    'position' => $request->integer('position'),
                    'currency' => LocaleText::blank($request->input('currency')),
                    'base_price' => $request->input('base_price'),
                    'discount_price' => $request->input('discount_price'),
                    'discount_percent' => $request->input('discount_percent'),
                    'discount_starts_at' => $request->input('discount_starts_at') ?: null,
                    'discount_ends_at' => $request->input('discount_ends_at') ?: null,
                    'duration_days' => $request->input('duration_days') ?: null,
                    'duration_nights' => $request->input('duration_nights') ?: null,
                ];
                if (filled($payload['currency'])) {
                    $payload['currency'] = strtoupper((string) $payload['currency']);
                }

                if ($tour) {
                    $tour->update($payload);
                } else {
                    $tour = Tour::query()->create($payload);
                }

                LocaleText::sync($tour, $request->input('translations', []), [
                    'title',
                    'slug',
                    'summary',
                    'description',
                    'departure_location',
                    'meeting_instructions',
                    'transport_details',
                    'meals',
                    'notes',
                    'requirements',
                    'restrictions',
                    'terms',
                    'cancellation',
                    'meta_title',
                    'meta_description',
                    'canonical_url',
                ], 'title');

                $tour->transports()->sync($this->ids($request->input('transport_ids', [])));
                $tour->services()->sync($this->ids($request->input('service_ids', [])));
                $this->syncDepartures($tour, $request);
                $this->syncStays($tour, $request);
                $days = $this->syncDays($tour, $request);
                $this->syncPoints($tour, $request, $days);
                $this->syncFaqs($tour, $request);
                $this->syncImages($tour, $request, $images);
                CatalogCache::flush();

                return $tour->refresh();
            });
        } catch (Throwable $exception) {
            foreach ($images as $path) {
                ImageStore::delete($path);
            }

            throw $exception;
        }
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, array{position: int}>
     */
    private function ids(array $ids): array
    {
        $sync = [];
        $position = 0;
        foreach ($ids as $id) {
            if (is_numeric($id)) {
                $sync[(int) $id] = ['position' => $position];
                $position++;
            }
        }

        return $sync;
    }

    private function syncDepartures(Tour $tour, Request $request): void
    {
        $tour->departures()->delete();
        foreach ($request->input('departures', []) as $row) {
            if (! is_array($row) || ! filled($row['departure_date'] ?? null) || ! filled($row['return_date'] ?? null)) {
                continue;
            }
            $tour->departures()->create([
                'departure_date' => $row['departure_date'],
                'return_date' => $row['return_date'],
                'is_published' => filter_var($row['is_published'] ?? false, FILTER_VALIDATE_BOOL),
            ]);
        }
    }

    private function syncStays(Tour $tour, Request $request): void
    {
        $tour->stays()->delete();
        $position = 0;
        foreach ($request->input('stays', []) as $row) {
            if (! is_array($row) || ! is_numeric($row['hotel_id'] ?? null)) {
                continue;
            }
            $stay = $tour->stays()->create([
                'hotel_id' => (int) $row['hotel_id'],
                'position' => $position,
            ]);
            $position++;
            $this->syncLoose($stay, is_array($row['translations'] ?? null) ? $row['translations'] : [], ['arrangement']);
        }
    }

    /**
     * @return array<int, int>
     */
    private function syncDays(Tour $tour, Request $request): array
    {
        $tour->days()->delete();
        $map = [];
        foreach ($request->input('itinerary', []) as $row) {
            if (! is_array($row) || ! is_numeric($row['day_number'] ?? null)) {
                continue;
            }
            $translations = is_array($row['translations'] ?? null) ? $row['translations'] : [];
            if (! $this->anyFilled($translations, ['title', 'body'])) {
                continue;
            }
            $day = $tour->days()->create(['day_number' => (int) $row['day_number']]);
            $this->syncLoose($day, $translations, ['title', 'body']);
            $map[(int) $row['day_number']] = $day->id;
        }

        return $map;
    }

    /**
     * @param  array<int, int>  $days
     */
    private function syncPoints(Tour $tour, Request $request, array $days): void
    {
        $tour->routePoints()->delete();
        $position = 0;
        foreach ($request->input('points', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $translations = is_array($row['translations'] ?? null) ? $row['translations'] : [];
            $lat = filled($row['latitude'] ?? null) ? $row['latitude'] : null;
            $lng = filled($row['longitude'] ?? null) ? $row['longitude'] : null;
            if (! $this->anyFilled($translations, ['name', 'description']) && $lat === null && $lng === null) {
                continue;
            }
            $dayNumber = is_numeric($row['day_number'] ?? null) ? (int) $row['day_number'] : null;
            $point = $tour->routePoints()->create([
                'itinerary_day_id' => $dayNumber && isset($days[$dayNumber]) ? $days[$dayNumber] : null,
                'position' => $position,
                'latitude' => $lat,
                'longitude' => $lng,
            ]);
            $position++;
            $this->syncLoose($point, $translations, ['name', 'description']);
        }
    }

    private function syncFaqs(Tour $tour, Request $request): void
    {
        $tour->faqs()->delete();
        $position = 0;
        foreach ($request->input('faqs', []) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $translations = is_array($row['translations'] ?? null) ? $row['translations'] : [];
            if (! $this->anyFilled($translations, ['question'])) {
                continue;
            }
            $faq = $tour->faqs()->create([
                'is_published' => filter_var($row['is_published'] ?? false, FILTER_VALIDATE_BOOL),
                'position' => $position,
            ]);
            $position++;
            LocaleText::sync($faq, $translations, ['question', 'answer'], 'question');
        }
    }

    /**
     * @param  list<string>  $paths
     */
    private function syncImages(Tour $tour, Request $request, array $paths): void
    {
        $remove = array_map('intval', (array) $request->input('remove_images', []));
        $tour->images()->whereIn('id', $remove)->get()->each(function (TourImage $image) {
            ImageStore::delete($image->path);
            $image->delete();
        });

        $position = (int) $tour->images()->max('position') + 1;
        $created = [];
        foreach ($paths as $path) {
            $created[] = $tour->images()->create([
                'path' => $path,
                'position' => $position,
                'is_cover' => false,
            ]);
            $position++;
        }

        $coverId = $request->input('cover_image');
        $images = $tour->images()->orderBy('position')->get();
        $cover = $images->firstWhere('id', (int) $coverId) ?? ($created[0] ?? $images->firstWhere('is_cover', true) ?? $images->first());
        $tour->images()->update(['is_cover' => false]);
        if ($cover) {
            $cover->update(['is_cover' => true]);
            $tour->update(['cover_path' => $cover->path]);
        } else {
            $tour->update(['cover_path' => null]);
        }
    }

    /**
     * @param  array<string, mixed>  $translations
     * @param  list<string>  $columns
     */
    private function syncLoose(ItineraryDay|RoutePoint|TourHotel $parent, array $translations, array $columns): void
    {
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = is_array($translations[$locale] ?? null) ? $translations[$locale] : [];
            $payload = ['locale' => $locale];
            $filled = false;
            foreach ($columns as $column) {
                $payload[$column] = LocaleText::blank(isset($row[$column]) ? (string) $row[$column] : null);
                if ($payload[$column] !== null) {
                    $filled = true;
                }
            }
            if ($filled) {
                $parent->translations()->updateOrCreate(['locale' => $locale], $payload);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $translations
     * @param  list<string>  $columns
     */
    private function anyFilled(array $translations, array $columns): bool
    {
        foreach ($translations as $row) {
            if (! is_array($row)) {
                continue;
            }
            foreach ($columns as $column) {
                if (filled($row[$column] ?? null)) {
                    return true;
                }
            }
        }

        return false;
    }
}
