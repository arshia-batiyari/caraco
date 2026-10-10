<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Support\ContentPresenter;
use App\Support\PublicCatalog;
use App\Support\SeoData;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        $slug = (string) request()->route('slug');
        $category = Category::query()
            ->where('is_published', true)
            ->whereHas('translations', fn ($query) => $query->where('locale', app()->getLocale())->where('slug', $slug))
            ->with('translations')
            ->first();
        abort_if($category === null, 404);
        $translation = $category->translations->firstWhere('locale', app()->getLocale());
        abort_if($translation === null, 404);

        $tours = $catalog->tours()->where('category_id', $category->id)->paginate(9);
        $url = route('categories.show', ['slug' => $translation->slug]);
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = $category->translations->firstWhere('locale', $locale);
            $links[$locale] = $row && filled($row->name) && filled($row->slug)
                ? route('categories.show', ['locale' => $locale, 'slug' => $row->slug])
                : route('home', ['locale' => $locale]);
        }

        return $presenter->render('pages.categories.show', [
            'category' => $category,
            'translation' => $translation,
            'tours' => $tours,
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
        ), $links);
    }
}
