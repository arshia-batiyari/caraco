<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $urls[] = route('home', ['locale' => $locale]);
            $urls[] = route('contact', ['locale' => $locale]);
        }

        Division::query()->where('is_published', true)->with('translations')->get()->each(function (Division $division) use (&$urls) {
            foreach ($division->translations as $translation) {
                if (filled($translation->title)) {
                    $urls[] = route('divisions.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });

        Project::query()->where('is_published', true)->with('translations')->get()->each(function (Project $project) use (&$urls) {
            foreach ($project->translations as $translation) {
                if (filled($translation->title)) {
                    $urls[] = route('projects.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });

        Page::query()->where('is_published', true)->with('translations')->get()->each(function (Page $page) use (&$urls) {
            foreach ($page->translations as $translation) {
                if (! filled($translation->title)) {
                    continue;
                }
                $urls[] = $page->key === 'about'
                    ? route('about', ['locale' => $translation->locale])
                    : route('pages.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
            }
        });

        $listingLocales = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            if (Division::query()->where('is_published', true)->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('title', '!=', ''))->exists()) {
                $urls[] = route('divisions.index', ['locale' => $locale]);
            }
            if (Project::query()->where('is_published', true)->whereHas('translations', fn ($query) => $query->where('locale', $locale)->where('title', '!=', ''))->exists()) {
                $listingLocales[] = route('projects.index', ['locale' => $locale]);
            }
        }
        $urls = array_merge($urls, $listingLocales);

        $body = ['<?xml version="1.0" encoding="UTF-8"?>', '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'];
        foreach (array_unique($urls) as $url) {
            $body[] = '<url><loc>'.htmlspecialchars($url, ENT_XML1).'</loc></url>';
        }
        $body[] = '</urlset>';

        return response(implode('', $body), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /setup',
            'Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml',
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
