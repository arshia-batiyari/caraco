<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\PreviewMedia;
use App\Support\PublicContent;
use App\Support\SeoData;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(PublicContent $content, ContentPresenter $presenter): View
    {
        $divisions = $content->divisions();
        if ($divisions->isEmpty()) {
            abort(404);
        }

        $home = $content->homepage();
        $heading = filled($home?->divisions_heading)
            ? (string) $home->divisions_heading
            : __('ui.nav.divisions');

        return $presenter->render('pages.divisions', [
            'divisions' => $divisions,
            'heading' => $heading,
        ], new SeoData(
            title: $presenter->documentTitle(null, $heading),
            canonical: route('divisions.index'),
        ), $content->alternatesForListing('divisions.index', Division::class));
    }

    public function show(PublicContent $content, ContentPresenter $presenter): View
    {
        $division = $this->find((string) request()->route('slug'));
        $translation = $division->translations->firstWhere('locale', app()->getLocale());
        $canonical = $presenter->canonical(
            $translation->canonical_url,
            route('divisions.show', ['slug' => $translation->slug])
        );

        return $presenter->render('pages.division', [
            'division' => $division,
            'translation' => $translation,
            'image' => PreviewMedia::division($division->image_path, $division->key),
            'schema' => $presenter->breadcrumb($translation->title, $canonical),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: $translation->introduction ?: ''),
            canonical: $canonical,
            image: PreviewMedia::division($division->image_path, $division->key) ?: ImageStore::url(\App\Models\Setting::getValue('og_image')),
        ), $content->alternatesForDivisions($division));
    }

    private function find(string $slug): Division
    {
        $division = Division::query()
            ->where('is_published', true)
            ->whereHas('translations', function ($query) use ($slug) {
                $query->where('locale', app()->getLocale())->where('slug', $slug);
            })
            ->with('translations')
            ->first();

        $translation = $division?->translations->firstWhere('locale', app()->getLocale());
        if (! $division || ! $translation || $translation->slug !== $slug || ! filled($translation->title)) {
            abort(404);
        }

        return $division;
    }
}
