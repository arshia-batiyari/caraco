<?php

namespace App\Http\Controllers;

use App\Models\Attraction;
use App\Models\FeedbackSubmission;
use App\Models\MedicalService;
use App\Models\Setting;
use App\Models\Tour;
use App\Support\ContentPresenter;
use App\Support\ImageStore;
use App\Support\PublicCatalog;
use App\Support\SeoData;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(PublicCatalog $catalog, ContentPresenter $presenter): View
    {
        $home = $catalog->homepage();
        $canonicalLocale = $home?->locale ?: app()->getLocale();
        $email = Setting::getValue('contact_email');
        $phone = Setting::getValue('contact_phone');

        return $presenter->render('pages.home', [
            'home' => $home,
            'video' => $catalog->video(),
            'heroImage' => ImageStore::url(Setting::getValue('hero_image')),
            'destinations' => $catalog->featuredDestinations(),
            'categories' => $catalog->categories(),
            'popular' => $catalog->tours()->where('is_featured', true)->limit(6)->get(),
            'discounted' => $catalog->tours()->discounted()->limit(6)->get(),
            'domestic' => $catalog->tours()->where('scope', 'domestic')->limit(4)->get(),
            'international' => $catalog->tours()->where('scope', 'international')->limit(4)->get(),
            'hotels' => $catalog->featuredHotels(),
            'filterOptions' => $catalog->filterOptions(),
            'inquiryTours' => Tour::query()->published()->with('translations')->orderBy('position')->get(),
            'privacy' => $catalog->privacyPage(),
            'hasMedical' => MedicalService::query()->published()->exists(),
            'hasAttractions' => Attraction::query()->published()->exists(),
            'reviews' => FeedbackSubmission::query()
                ->where('type', '!=', 'complaint')
                ->whereHas('review', fn ($inner) => $inner->where('status', 'approved'))
                ->with(['review', 'tour.translations'])
                ->latest('id')
                ->limit(3)
                ->get(),
            'schema' => $this->schema($email, $phone, $home?->hero_lede),
        ], new SeoData(
            title: $presenter->documentTitle($home?->meta_title, filled($home?->hero_title) ? (string) $home->hero_title : 'KARAKO'),
            description: (string) ($home?->meta_description ?: $home?->hero_lede ?: ''),
            canonical: $presenter->canonical($home?->canonical_url, route('home', ['locale' => $canonicalLocale])),
            image: ImageStore::url(Setting::getValue('hero_image')),
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function schema(?string $email, ?string $phone, ?string $description): ?array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'KARAKO',
            'url' => rtrim((string) config('app.url'), '/').'/'.app()->getLocale(),
        ];
        if (filled($description)) {
            $data['description'] = $description;
        }
        if (filled($email) || filled($phone)) {
            $data['publisher'] = array_filter([
                '@type' => 'Organization',
                'name' => 'KARAKO',
                'email' => $email,
                'telephone' => $phone,
            ]);
        }

        return $data;
    }
}
