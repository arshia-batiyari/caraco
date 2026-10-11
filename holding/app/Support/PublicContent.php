<?php

namespace App\Support;

use App\Models\Division;
use App\Models\HomepageTranslation;
use App\Models\Page;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Support\Collection;

class PublicContent
{
    /** @var Collection<int, Division>|null */
    private ?Collection $divisionCache = null;

    /** @var Collection<int, Project>|null */
    private ?Collection $projectCache = null;

    public function homepage(): ?HomepageTranslation
    {
        return HomepageTranslation::forLocale();
    }

    /**
     * @return Collection<int, Division>
     */
    public function divisions(): Collection
    {
        return $this->divisionCache ??= Division::query()
            ->where('is_published', true)
            ->orderBy('position')
            ->with('translations')
            ->get()
            ->filter(fn (Division $division) => filled($division->translated()?->title))
            ->values();
    }

    /**
     * @return Collection<int, Project>
     */
    public function projects(): Collection
    {
        return $this->projectCache ??= Project::query()
            ->where('is_published', true)
            ->orderBy('position')
            ->orderBy('id')
            ->with('translations')
            ->get()
            ->filter(fn (Project $project) => filled($project->translated()?->title))
            ->values();
    }

    public function about(): ?Page
    {
        $page = Page::query()->where('key', 'about')->where('is_published', true)->with('translations')->first();

        return $page && filled($page->translated()?->title) ? $page : null;
    }

    public function pageBySlug(string $slug): ?Page
    {
        $page = Page::query()
            ->where('is_published', true)
            ->where(function ($query) {
                $query->whereNull('key')->orWhere('key', '!=', 'about');
            })
            ->whereHas('translations', fn ($query) => $query->where('slug', $slug)->where('locale', app()->getLocale()))
            ->with('translations')
            ->first();

        if (! $page) {
            return null;
        }

        $translation = $page->translations->firstWhere('locale', app()->getLocale());

        return $translation && $translation->slug === $slug ? $page : null;
    }

    /**
     * @return array{type: string, src: ?string, poster: ?string, provider: ?string}|null
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
                'src' => ImageStore::url($path),
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

    /**
     * @param  class-string<Division|Project|Page>  $modelClass
     * @return array<string, string>
     */
    public function alternatesForListing(string $route, string $modelClass): array
    {
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $exists = $modelClass::query()
                ->where('is_published', true)
                ->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('title', '!=', ''))
                ->exists();
            $links[$locale] = $exists
                ? route($route, ['locale' => $locale])
                : route('home', ['locale' => $locale]);
        }

        return $links;
    }

    /**
     * @return array<string, string>
     */
    public function alternatesForDivisions(Division $division): array
    {
        $links = [];
        foreach ($division->translations as $translation) {
            $links[$translation->locale] = route('divisions.show', [
                'locale' => $translation->locale,
                'slug' => $translation->slug,
            ]);
        }

        return $this->fillMissing($links);
    }

    /**
     * @return array<string, string>
     */
    public function alternatesForProjects(Project $project): array
    {
        $links = [];
        foreach ($project->translations as $translation) {
            $links[$translation->locale] = route('projects.show', [
                'locale' => $translation->locale,
                'slug' => $translation->slug,
            ]);
        }

        return $this->fillMissing($links);
    }

    /**
     * @return array<string, string>
     */
    public function alternatesForPage(Page $page, string $route): array
    {
        $links = [];
        foreach ($page->translations as $translation) {
            $params = ['locale' => $translation->locale];
            if ($route !== 'about') {
                $params['slug'] = $translation->slug;
            }
            $links[$translation->locale] = route($route, $params);
        }

        return $this->fillMissing($links);
    }

    /**
     * @param  array<string, string>  $links
     * @return array<string, string>
     */
    private function fillMissing(array $links): array
    {
        foreach (array_keys(config('karako.locales')) as $locale) {
            $links[$locale] ??= route('home', ['locale' => $locale]);
        }

        return $links;
    }
}
