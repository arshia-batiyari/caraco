<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Faq;
use App\Models\HomepageTranslation;
use App\Models\Hotel;
use App\Models\Page;
use App\Models\Setting;
use App\Models\Tour;
use App\Models\TransportOption;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PublicCatalog
{
    public function homepage(): ?HomepageTranslation
    {
        return HomepageTranslation::forLocale();
    }

    /**
     * @return array{type: string, src: string, poster: ?string, provider: ?string}|null
     */
    public function video(): ?array
    {
        $type = Setting::getValue('video_type', 'none');
        $poster = ImageStore::url(Setting::getValue('poster_path'));

        if ($type === 'file') {
            $path = Setting::getValue('video_path');
            if (! filled($path)) {
                return null;
            }

            return [
                'type' => 'file',
                'src' => (string) ImageStore::url($path),
                'poster' => $poster,
                'provider' => null,
            ];
        }

        if ($type === 'url') {
            $embed = VideoEmbed::fromUrl(Setting::getValue('video_url'));
            if ($embed === null) {
                return null;
            }

            return [
                'type' => 'embed',
                'src' => $embed['src'],
                'poster' => $poster,
                'provider' => $embed['provider'],
            ];
        }

        return null;
    }

    public function tours(): Builder
    {
        return Tour::query()
            ->published()
            ->visibleInLocale()
            ->with($this->cardRelations())
            ->orderBy('position')
            ->orderByDesc('id');
    }

    /**
     * @return array<int|string, mixed>
     */
    public function cardRelations(): array
    {
        $locales = array_values(array_unique([app()->getLocale(), (string) config('app.fallback_locale')]));

        return [
            'translations' => fn ($query) => $query->whereIn('locale', $locales),
            'destination.translations' => fn ($query) => $query->whereIn('locale', $locales),
            'category.translations' => fn ($query) => $query->whereIn('locale', $locales),
            'departures' => fn ($query) => $query->where('is_published', true)->orderBy('departure_date'),
            'transports.translations' => fn ($query) => $query->whereIn('locale', $locales),
        ];
    }

    /**
     * @return Collection<int, Destination>
     */
    public function featuredDestinations(): Collection
    {
        return Destination::query()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with('translations')
            ->orderBy('position')
            ->limit(8)
            ->get()
            ->filter(fn (Destination $destination) => $destination->publicUrl('destinations.show') !== null)
            ->values();
    }

    /**
     * @return Collection<int, Category>
     */
    public function categories(): Collection
    {
        return Category::query()
            ->where('is_published', true)
            ->with('translations')
            ->orderBy('position')
            ->get()
            ->filter(fn (Category $category) => $category->publicUrl('categories.show') !== null)
            ->values();
    }

    /**
     * @return array{destinations: Collection, cities: Collection, categories: Collection, transports: Collection}
     */
    public function filterOptions(): array
    {
        $key = CatalogCache::KEY;

        return Cache::remember($key, 600, function () {
            return [
                'destinations' => Destination::query()->where('is_published', true)->with('translations')->orderBy('position')->get(),
                'cities' => Destination::query()->where('is_published', true)->where('type', 'city')->with('translations')->orderBy('position')->get(),
                'categories' => Category::query()->where('is_published', true)->with('translations')->orderBy('position')->get(),
                'transports' => TransportOption::query()->where('is_published', true)->with('translations')->orderBy('position')->get(),
            ];
        });
    }

    /**
     * @return Collection<int, Hotel>
     */
    public function featuredHotels(): Collection
    {
        return Hotel::query()
            ->where('is_published', true)
            ->where('is_featured', true)
            ->with(['translations', 'images'])
            ->orderBy('position')
            ->limit(6)
            ->get()
            ->filter(fn (Hotel $hotel) => $hotel->publicTranslation() !== null)
            ->values();
    }

    public function privacyPage(): ?Page
    {
        $page = Page::query()->where('key', 'privacy')->where('is_published', true)->with('translations')->first();

        return $page && $page->publicTranslation() ? $page : null;
    }

    /**
     * @return Collection<int, Faq>
     */
    public function globalFaqs(): Collection
    {
        return Faq::query()
            ->whereNull('tour_id')
            ->where('is_published', true)
            ->with('translations')
            ->orderBy('position')
            ->get()
            ->filter(fn (Faq $faq) => $faq->publicTranslation() !== null)
            ->values();
    }
}
