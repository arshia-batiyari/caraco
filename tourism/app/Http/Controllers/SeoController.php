<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use App\Models\Page;
use App\Models\Tour;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $urls = [];
        foreach (array_keys(config('karako.locales')) as $locale) {
            $urls[] = route('home', ['locale' => $locale]);
            $urls[] = route('consultation', ['locale' => $locale]);
            $urls[] = route('feedback.index', ['locale' => $locale]);
        }

        if (Tour::query()->published()->exists()) {
            foreach (array_keys(config('karako.locales')) as $locale) {
                $urls[] = route('tours.index', ['locale' => $locale]);
            }
        }

        Tour::query()->published()->with('translations')->get()->each(function (Tour $tour) use (&$urls) {
            foreach ($tour->translations as $translation) {
                if (filled($translation->title) && filled($translation->slug)) {
                    $urls[] = route('tours.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });

        Destination::query()->where('is_published', true)->with('translations')->get()->each(function (Destination $destination) use (&$urls) {
            foreach ($destination->translations as $translation) {
                if (filled($translation->name) && filled($translation->slug)) {
                    $urls[] = route('destinations.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });

        Category::query()->where('is_published', true)->with('translations')->get()->each(function (Category $category) use (&$urls) {
            foreach ($category->translations as $translation) {
                if (filled($translation->name) && filled($translation->slug)) {
                    $urls[] = route('categories.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });

        Page::query()->where('is_published', true)->with('translations')->get()->each(function (Page $page) use (&$urls) {
            foreach ($page->translations as $translation) {
                if (filled($translation->title) && filled($translation->slug)) {
                    $urls[] = route('pages.show', ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });

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
            'Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml',
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
