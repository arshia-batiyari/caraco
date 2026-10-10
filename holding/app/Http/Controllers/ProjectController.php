<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\PublicContent;
use App\Support\SeoData;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(PublicContent $content, ContentPresenter $presenter): View
    {
        $locale = app()->getLocale();
        $query = Project::query()
            ->where('is_published', true)
            ->whereHas('translations', fn ($builder) => $builder->where('locale', $locale)->where('title', '!=', ''))
            ->orderBy('position')
            ->orderBy('id')
            ->with('translations');

        if (! $query->exists()) {
            abort(404);
        }

        $home = $content->homepage();
        $heading = filled($home?->projects_heading)
            ? (string) $home->projects_heading
            : __('ui.nav.projects');

        return $presenter->render('pages.projects', [
            'projects' => $query->paginate(9)->withQueryString(),
            'heading' => $heading,
        ], new SeoData(
            title: $presenter->documentTitle(null, $heading),
            canonical: url()->current(),
        ), $content->alternatesForListing('projects.index', Project::class));
    }

    public function show(PublicContent $content, ContentPresenter $presenter): View
    {
        $slug = (string) request()->route('slug');
        $project = Project::query()
            ->where('is_published', true)
            ->whereHas('translations', function ($query) use ($slug) {
                $query->where('locale', app()->getLocale())->where('slug', $slug);
            })
            ->with('translations')
            ->first();

        $translation = $project?->translations->firstWhere('locale', app()->getLocale());
        if (! $project || ! $translation || $translation->slug !== $slug || ! filled($translation->title)) {
            abort(404);
        }

        $canonical = $presenter->canonical(
            $translation->canonical_url,
            route('projects.show', ['slug' => $translation->slug])
        );

        return $presenter->render('pages.project', [
            'project' => $project,
            'translation' => $translation,
            'image' => ImageStore::url($project->image_path),
            'schema' => $presenter->breadcrumb($translation->title, $canonical),
        ], new SeoData(
            title: $presenter->documentTitle($translation->meta_title, $translation->title),
            description: (string) ($translation->meta_description ?: $translation->summary ?: ''),
            canonical: $canonical,
            image: ImageStore::url($project->image_path) ?: ImageStore::url(\App\Models\Setting::getValue('og_image')),
        ), $content->alternatesForProjects($project));
    }
}
