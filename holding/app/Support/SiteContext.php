<?php

namespace App\Support;

use App\Models\ContactTranslation;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Setting;
use App\Models\SocialLink;
use Illuminate\Support\Collection;

class SiteContext
{
    public function __construct(private PublicContent $content) {}

    /**
     * @return list<array{url: string, label: string, active: bool, external: bool}>
     */
    public function links(string $location): array
    {
        $items = NavigationItem::query()
            ->where('location', $location)
            ->where('is_published', true)
            ->orderBy('position')
            ->with(['translations', 'page.translations'])
            ->get();

        if ($items->isEmpty() && $location === 'header') {
            return $this->fallbackLinks();
        }

        return $items
            ->map(fn (NavigationItem $item) => $this->resolve($item))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, SocialLink>
     */
    public function socialLinks(): Collection
    {
        return SocialLink::query()
            ->where('is_published', true)
            ->orderBy('position')
            ->get()
            ->filter(fn (SocialLink $link) => HttpUrl::valid($link->url));
    }

    public function contact(): ?ContactTranslation
    {
        return ContactTranslation::forLocale();
    }

    public function email(): ?string
    {
        return Setting::getValue('contact_email');
    }

    public function phone(): ?string
    {
        return Setting::getValue('contact_phone');
    }

    /**
     * @return array{url: string, label: string, active: bool, external: bool}|null
     */
    private function resolve(NavigationItem $item): ?array
    {
        $label = $item->label();
        if ($label === '') {
            return null;
        }

        if ($item->kind === 'url') {
            if (! HttpUrl::valid($item->url)) {
                return null;
            }

            return [
                'url' => $item->url,
                'label' => $label,
                'active' => false,
                'external' => true,
            ];
        }

        if ($item->kind === 'page') {
            $page = $item->page;
            $translation = $page?->is_published ? $page->translated() : null;
            if (! $page || ! $translation || ! filled($translation->title)) {
                return null;
            }

            $route = $page->key === 'about' ? 'about' : 'pages.show';
            $params = $page->key === 'about' ? [] : ['slug' => $translation->slug];

            return [
                'url' => route($route, $params),
                'label' => $label,
                'active' => $page->key === 'about' ? request()->routeIs('about') : request()->routeIs('pages.show'),
                'external' => false,
            ];
        }

        return match ($item->route_name) {
            'home' => $this->internal('home', $label, request()->routeIs('home')),
            'about' => $this->aboutLink($label),
            'divisions.index' => $this->content->divisions()->isEmpty()
                ? null
                : $this->internal('divisions.index', $label, request()->routeIs('divisions.*')),
            'projects.index' => $this->content->projects()->isEmpty()
                ? null
                : $this->internal('projects.index', $label, request()->routeIs('projects.*')),
            'contact' => $this->internal('contact', $label, request()->routeIs('contact')),
            default => null,
        };
    }

    /**
     * @return array{url: string, label: string, active: bool, external: bool}|null
     */
    private function aboutLink(string $label): ?array
    {
        $page = Page::query()->where('key', 'about')->where('is_published', true)->with('translations')->first();
        if (! $page || ! filled($page->translated()?->title)) {
            return null;
        }

        return $this->internal('about', $label, request()->routeIs('about'));
    }

    /**
     * @return array{url: string, label: string, active: bool, external: bool}
     */
    private function internal(string $route, string $label, bool $active): array
    {
        return [
            'url' => route($route),
            'label' => $label,
            'active' => $active,
            'external' => false,
        ];
    }

    /**
     * @return list<array{url: string, label: string, active: bool, external: bool}>
     */
    private function fallbackLinks(): array
    {
        return [
            $this->internal('home', __('ui.nav.home'), request()->routeIs('home')),
            $this->internal('contact', __('ui.nav.contact'), request()->routeIs('contact')),
        ];
    }
}
