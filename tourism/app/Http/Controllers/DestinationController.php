<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\PublicCatalog;
use App\Support\SeoData;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function show(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        return $this->place($catalog, $presenter, Destination::class, 'destinations.show');
    }

    /**
     * @param  class-string<Destination>  $model
     */
    private function place(PublicCatalog $catalog, ContentPresenter $presenter, string $model, string $route): View
    {
        $slug = (string) request()->route('slug');
        $destination = $model::query()
            ->where('is_published', true)
            ->whereHas('translations', fn ($query) => $query->where('locale', app()->getLocale())->where('slug', $slug))
            ->with('translations')
            ->first();
        abort_if($destination === null, 404);
        $translation = $destination->translations->firstWhere('locale', app()->getLocale());
        abort_if($translation === null, 404);

        $tours = $catalog->tours()->forDestination($destination->id)->paginate(9);
        $url = route($route, ['slug' => $translation->slug]);

        return $presenter->render('pages.destinations.show', [
            'destination' => $destination,
            'translation' => $translation,
            'tours' => $tours,
            'image' => ImageStore::url($destination->image_path),
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => __('ui.nav.tours'), 'url' => route('tours.index')],
                ['label' => $translation->name, 'url' => $url],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => __('ui.nav.tours'), 'url' => route('tours.index')],
                ['name' => $translation->name, 'url' => $url],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->name),
            description: (string) ($translation->meta_description ?: $translation->summary ?: ''),
            canonical: $presenter->canonical($translation->canonical_url, $url),
            image: ImageStore::url($destination->image_path),
        ), $this->alternates($destination, $route, 'name'));
    }

    /**
     * @return array<string, string>
     */
    private function alternates(Destination $record, string $route, string $label): array
    {
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = $record->translations->firstWhere('locale', $locale);
            $links[$locale] = $row && filled($row->{$label}) && filled($row->slug)
                ? route($route, ['locale' => $locale, 'slug' => $row->slug])
                : route('home', ['locale' => $locale]);
        }

        return $links;
    }
}
