<?php

namespace App\Http\Controllers;

use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\PublicContent;
use App\Support\SeoData;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(PublicContent $content, ContentPresenter $presenter): View
    {
        $page = $content->about();
        $translation = $page?->publicTranslation();
        if (! $page || ! $translation) {
            abort(404);
        }

        $canonical = $presenter->canonical(
            $translation->canonical_url,
            route('about', ['locale' => $translation->locale])
        );

        return $presenter->render('pages.article', [
            'title' => $translation->title,
            'body' => $translation->body,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => $translation->title],
            ],
            'schema' => $presenter->breadcrumb($translation->title, $canonical),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: ''),
            canonical: $canonical,
            image: ImageStore::url(\App\Models\Setting::getValue('og_image')),
        ), $content->alternatesForPage($page, 'about'));
    }

    public function show(PublicContent $content, ContentPresenter $presenter): View
    {
        $page = $content->pageBySlug((string) request()->route('slug'));
        $translation = $page?->translations->firstWhere('locale', app()->getLocale());
        if (! $page || ! $translation || ! filled($translation->title)) {
            abort(404);
        }

        $canonical = $presenter->canonical(
            $translation->canonical_url,
            route('pages.show', ['slug' => $translation->slug])
        );

        return $presenter->render('pages.article', [
            'title' => $translation->title,
            'body' => $translation->body,
            'crumbs' => [
                ['label' => __('ui.nav.home'), 'url' => route('home')],
                ['label' => $translation->title],
            ],
            'schema' => $presenter->breadcrumb($translation->title, $canonical),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: ''),
            canonical: $canonical,
            image: ImageStore::url(\App\Models\Setting::getValue('og_image')),
        ), $content->alternatesForPage($page, 'pages.show'));
    }
}
