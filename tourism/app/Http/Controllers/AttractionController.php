<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\AttractionCategory;
use App\Models\Destination;
use App\Support\AlternateLinks;
use App\Support\AttractionFilter;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\LocaleText;
use App\Support\PublishedRecord;
use App\Support\SeoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttractionController extends Controller
{
    public function index(Request $request, ContentPresenter $presenter): View|RedirectResponse
    {
        $filtered = AttractionFilter::requested($request);
        $filter = AttractionFilter::fromRequest($request);
        if ($filtered && $filter === null) {
            return redirect()->route('attractions.index')->withErrors(['filters' => __('ui.filters.invalid')]);
        }

        $query = Attraction::query()->published()->with([
            'translations',
            'category.translations',
            'destination.translations',
            'destination.parent.translations',
        ])->orderBy('position')->orderBy('id');
        if ($filter) {
            $filter->apply($query);
        }

        $canonical = route('attractions.index');

        return $presenter->render('pages.attractions.index', [
            'attractions' => $query->paginate(9)->withQueryString(),
            'filters' => $filter?->values() ?? [],
            'filtered' => $filtered,
            'countries' => Destination::query()->where('is_published', true)->where('type', 'country')->with('translations')->orderBy('position')->get(),
            'cities' => Destination::query()->where('is_published', true)->where('type', 'city')->with('translations')->orderBy('position')->get(),
            'categories' => AttractionCategory::query()->published()->with('translations')->orderBy('position')->get(),
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.attractions.title'), 'url' => $canonical],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.attractions.title'), 'url' => $canonical],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle(null, __('ui.attractions.title')),
            description: __('ui.attractions.description'),
            canonical: $canonical,
            robots: $filtered ? 'noindex, follow' : 'index, follow',
        ), AlternateLinks::section('attractions.index'));
    }

    public function show(ContentPresenter $presenter): View
    {
        [$record, $translation] = PublishedRecord::find(Attraction::class, (string) request()->route('slug'), 'title', [
            'images.translations',
            'category.translations',
            'destination.translations',
            'destination.parent.translations',
            'tours.translations',
        ]);
        $url = route('attractions.show', ['slug' => $translation->slug]);
        $place = $record->publicPlace();

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'TouristAttraction',
            'name' => $translation->title,
            'url' => $url,
        ];
        if (filled($translation->summary)) {
            $schema['description'] = $translation->summary;
        }
        if ($record->showsUnesco()) {
            $schema['keywords'] = __('ui.attractions.unesco');
        }

        return $presenter->render('pages.attractions.show', [
            'record' => $record,
            'translation' => $translation,
            'cover' => ImageStore::url($record->cover_path),
            'place' => $place,
            'paragraphs' => LocaleText::paragraphs($translation->description),
            'visitor' => LocaleText::paragraphs($translation->visitor_info),
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.attractions.title'), 'url' => route('attractions.index')],
                ['label' => $translation->title, 'url' => $url],
            ],
            'schema' => $schema,
            'breadcrumbs' => $presenter->breadcrumbs([
                ['name' => __('ui.attractions.title'), 'url' => route('attractions.index')],
                ['name' => $translation->title, 'url' => $url],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: $translation->summary ?: ''),
            canonical: $presenter->canonical($translation->canonical_url, $url),
            image: ImageStore::url($record->cover_path),
        ), AlternateLinks::record($record, 'attractions.show', 'title'));
    }
}
