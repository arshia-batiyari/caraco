<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Support\ContentPresenter;
use App\Support\SeoData;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(ContentPresenter $presenter): View
    {
        $slug = (string) request()->route('slug');
        $page = Page::query()
            ->where('is_published', true)
            ->whereHas('translations', fn ($query) => $query->where('locale', app()->getLocale())->where('slug', $slug))
            ->with('translations')
            ->first();
        abort_if($page === null, 404);
        $translation = $page->translations->firstWhere('locale', app()->getLocale());
        abort_if($translation === null, 404);

        $url = route('pages.show', ['slug' => $translation->slug]);
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $row = $page->translations->firstWhere('locale', $locale);
            $links[$locale] = $row && filled($row->title) && filled($row->slug)
                ? route('pages.show', ['locale' => $locale, 'slug' => $row->slug])
                : route('home', ['locale' => $locale]);
        }

        return $presenter->render('pages.info', [
            'translation' => $translation,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => $translation->title, 'url' => $url],
            ],
            'schema' => $presenter->breadcrumbs([
                ['name' => $translation->title, 'url' => $url],
            ]),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: ''),
            canonical: $presenter->canonical($translation->canonical_url, $url),
        ), $links);
    }
}
