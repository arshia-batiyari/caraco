<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\Category;
use App\Models\Destination;
use App\Models\MedicalCategory;
use App\Models\MedicalDoctor;
use App\Models\MedicalProvider;
use App\Models\MedicalService;
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

        if (MedicalService::query()->published()->exists() || MedicalCategory::query()->published()->exists()) {
            foreach (array_keys(config('karako.locales')) as $locale) {
                $urls[] = route('medical.index', ['locale' => $locale]);
                $urls[] = route('medical.services', ['locale' => $locale]);
            }
        }
        $this->translated($urls, MedicalService::class, 'medical.services.show', 'name');
        $this->translated($urls, MedicalCategory::class, 'medical.categories.show', 'name');
        $this->translated($urls, MedicalProvider::class, 'medical.providers.show', 'name');
        $this->translated($urls, MedicalDoctor::class, 'medical.doctors.show', 'name');

        if (Attraction::query()->published()->exists()) {
            foreach (array_keys(config('karako.locales')) as $locale) {
                $urls[] = route('attractions.index', ['locale' => $locale]);
            }
        }
        $this->translated($urls, Attraction::class, 'attractions.show', 'title');

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

    /**
     * @param  list<string>  $urls
     * @param  class-string  $class
     */
    private function translated(array &$urls, string $class, string $route, string $label): void
    {
        $class::query()->published()->with('translations')->get()->each(function ($record) use (&$urls, $route, $label) {
            foreach ($record->translations as $translation) {
                if (filled($translation->{$label}) && filled($translation->slug)) {
                    $urls[] = route($route, ['locale' => $translation->locale, 'slug' => $translation->slug]);
                }
            }
        });
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
