<?php

namespace App\Http\Controllers;

use App\Models\ProfileTranslation;
use App\Models\Setting;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\PublicContent;
use App\Support\SeoData;
use App\Support\SiteContext;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PublicContent $content, ContentPresenter $presenter, SiteContext $site): View
    {
        $home = $content->homepage();
        $canonicalLocale = $home?->locale ?: app()->getLocale();

        return $presenter->render('pages.home', [
            'home' => $home,
            'divisions' => $content->divisions(),
            'projects' => $content->projects()->take(3),
            'video' => $content->video(),
            'heroImage' => ImageStore::url(Setting::getValue('hero_image')),
            'introImage' => ImageStore::url(Setting::getValue('intro_image')),
            'schema' => $this->schema($site),
        ], new SeoData(
            title: $presenter->documentTitle($home?->meta_title, filled($home?->hero_title) ? (string) $home->hero_title : 'KARAKO'),
            description: (string) ($home?->meta_description ?: $home?->hero_lede ?: ''),
            canonical: $presenter->canonical($home?->canonical_url, route('home', ['locale' => $canonicalLocale])),
            image: ImageStore::url(Setting::getValue('og_image')) ?: ImageStore::url(Setting::getValue('hero_image')),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(SiteContext $site): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => Setting::getValue('company_name', 'KARAKO'),
            'url' => rtrim((string) config('app.url'), '/'),
        ];

        $summary = ProfileTranslation::forLocale()?->summary;
        if (filled($summary)) {
            $data['description'] = $summary;
        }
        if (filled($site->email())) {
            $data['email'] = $site->email();
        }
        if (filled($site->phone())) {
            $data['telephone'] = $site->phone();
        }

        $address = $site->contact()?->address;
        if (filled($address)) {
            $data['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $address,
            ];
        }

        $sameAs = $site->socialLinks()->pluck('url')->filter()->values()->all();
        if ($sameAs !== []) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }
}
