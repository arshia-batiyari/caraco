<?php

namespace App\Support;

use Illuminate\View\View;

class ContentPresenter
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>|null  $alternates
     */
    public function render(string $view, array $data, SeoData $seo, ?array $alternates = null): View
    {
        request()->attributes->set('alternates', $alternates ?? $this->samePath());

        return view($view, $data + ['seo' => $seo]);
    }

    /**
     * @return array<string, string>
     */
    public function samePath(): array
    {
        $links = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $links[$locale] = LocaleUrl::forLocale($locale);
        }

        return $links;
    }

    public function documentTitle(?string $meta, string $fallback): string
    {
        if (filled($meta)) {
            return $meta;
        }

        $fallback = trim($fallback);

        return $fallback === '' || $fallback === 'KARAKO' ? 'KARAKO' : $fallback.' — KARAKO';
    }

    public function canonical(?string $custom, string $fallback): string
    {
        return HttpUrl::valid($custom) ? trim((string) $custom) : $fallback;
    }

    /**
     * @return array<string, mixed>
     */
    public function breadcrumb(string $name, string $url): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('ui.nav.home'),
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $name,
                    'item' => $url,
                ],
            ],
        ];
    }
}
