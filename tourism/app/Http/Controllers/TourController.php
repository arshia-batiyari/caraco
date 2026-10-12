<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Tour;
use App\Support\ContentPresenter;
use App\Support\PreviewMedia;
use App\Support\PublicCatalog;
use App\Support\RouteMap;
use App\Support\SeoData;
use App\Support\TourFilter;
use App\Support\TourPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TourController extends Controller
{
    public function index(Request $request, PublicCatalog $catalog, ContentPresenter $presenter): View|RedirectResponse
    {
        $filtered = TourFilter::requested($request);
        $filter = TourFilter::fromRequest($request);
        if ($filtered && $filter === null) {
            return redirect()->route('tours.index')->withErrors(['filters' => __('ui.filters.invalid')]);
        }

        $query = $catalog->tours();
        if ($filter) {
            $filter->apply($query);
        }

        $tours = $query->paginate(9)->withQueryString();
        $canonical = route('tours.index');

        return $presenter->render('pages.tours.index', [
            'tours' => $tours,
            'filters' => $filter?->values() ?? [],
            'options' => $catalog->filterOptions(),
            'filtered' => $filtered,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.nav.tours'), 'url' => $canonical],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.nav.tours'), 'url' => $canonical],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle(null, __('ui.tours.index_title')),
            description: __('ui.tours.index_description'),
            canonical: $canonical,
            robots: $filtered ? 'noindex, follow' : 'index, follow',
        ));
    }

    public function show(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        $slug = (string) request()->route('slug');
        $tour = Tour::query()
            ->published()
            ->whereHas('translations', fn ($query) => $query->where('locale', app()->getLocale())->where('slug', $slug))
            ->with([
                'translations',
                'destination.translations',
                'category.translations',
                'travelStyle.translations',
                'departures',
                'transports.translations',
                'services.translations',
                'stays.hotel.translations',
                'stays.hotel.images',
                'stays.hotel.facilities.translations',
                'stays.translations',
                'images',
                'days.translations',
                'routePoints.translations',
                'faqs.translations',
            ])
            ->first();

        abort_if($tour === null, 404);

        $translation = $tour->translations->firstWhere('locale', app()->getLocale());
        abort_if($translation === null, 404);

        $related = Tour::query()
            ->published()
            ->visibleInLocale()
            ->where('id', '!=', $tour->id)
            ->where(function ($query) use ($tour) {
                if ($tour->destination_id) {
                    $query->orWhere('destination_id', $tour->destination_id);
                }
                if ($tour->category_id) {
                    $query->orWhere('category_id', $tour->category_id);
                }
            })
            ->with($catalog->cardRelations())
            ->limit(3)
            ->get()
            ->filter(fn (Tour $item) => $item->publicUrl('tours.show') !== null)
            ->values();

        if (! $tour->destination_id && ! $tour->category_id) {
            $related = collect();
        }

        $faqs = $tour->faqs->where('is_published', true)->filter(fn (Faq $faq) => $faq->publicTranslation() !== null)->values()
            ->concat($catalog->globalFaqs());

        $url = route('tours.show', ['slug' => $translation->slug]);
        $points = $tour->routePoints;
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristTrip',
            'name' => $translation->title,
            'url' => $url,
        ];
        if (filled($translation->summary) || filled($translation->description)) {
            $schema['description'] = (string) ($translation->summary ?: $translation->description);
        }
        $image = PreviewMedia::tour($tour);
        if ($image) {
            $schema['image'] = $image;
        }
        $amount = TourPrice::effectiveAmount($tour);
        if ($amount !== null && filled($tour->currency)) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $amount,
                'priceCurrency' => $tour->currency,
            ];
        }
        if ($tour->days->isNotEmpty()) {
            $schema['itinerary'] = [
                '@type' => 'ItemList',
                'itemListElement' => $tour->days->values()->map(function ($day, $index) {
                    $label = $day->publicTranslation();

                    return [
                        '@type' => 'ListItem',
                        'position' => $index + 1,
                        'name' => filled($label?->title) ? $label->title : __('ui.tours.day', ['n' => $day->day_number]),
                    ];
                })->all(),
            ];
        }

        return $presenter->render('pages.tours.show', [
            'tour' => $tour,
            'translation' => $translation,
            'related' => $related,
            'faqs' => $faqs,
            'map' => RouteMap::svg($points),
            'mapPoints' => RouteMap::coordinates($points),
            'tiles' => filled(config('karako.map.tiles')) ? route('map.tiles', ['z' => '__z', 'x' => '__x', 'y' => '__y']) : null,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.nav.tours'), 'url' => route('tours.index')],
                ['label' => $translation->title, 'url' => $url],
            ],
            'schema' => $schema,
            'breadcrumbs' => $presenter->breadcrumbs([
                ['name' => __('ui.nav.tours'), 'url' => route('tours.index')],
                ['name' => $translation->title, 'url' => $url],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: $translation->summary ?: ''),
            canonical: $presenter->canonical($translation->canonical_url, $url),
            image: $image,
        ), $this->alternates($tour));
    }

    /**
     * @return array<string, string>
     */
    private function alternates(Tour $tour): array
    {
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = $tour->translations->firstWhere('locale', $locale);
            $links[$locale] = $row && filled($row->title) && filled($row->slug)
                ? route('tours.show', ['locale' => $locale, 'slug' => $row->slug])
                : route('home', ['locale' => $locale]);
        }

        return $links;
    }
}
