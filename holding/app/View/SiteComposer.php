<?php

namespace App\View;

use App\Models\ProfileTranslation;
use App\Support\LocaleUrl;
use App\Support\SeoData;
use App\Support\SiteContext;
use Illuminate\View\View;

class SiteComposer
{
    public function __construct(private SiteContext $site) {}

    public function compose(View $view): void
    {
        $alternates = request()->attributes->get('alternates');
        if (! is_array($alternates)) {
            $alternates = [];
            foreach (array_keys(config('karako.locales')) as $code) {
                $alternates[$code] = LocaleUrl::forLocale($code);
            }
            request()->attributes->set('alternates', $alternates);
        }

        $named = request()->route()?->getName();
        $noindex = $named === null || in_array($named, ['foundations', 'credits'], true);
        $existing = $view->getData();

        $view->with([
            'brandLine' => __('ui.brand.holding'),
            'footerSummary' => ProfileTranslation::forLocale()?->summary,
            'navLinks' => $this->site->links('header'),
            'footerLinks' => $this->site->links('footer'),
            'socialLinks' => $this->site->socialLinks(),
            'contactDetails' => $this->site->contact(),
            'contactEmail' => $this->site->email(),
            'contactPhone' => $this->site->phone(),
            'relatedUrl' => config('karako.related_url') ?: null,
            'relatedLabel' => __('ui.related.tourism'),
            'alternates' => $alternates,
            'seo' => $existing['seo'] ?? new SeoData(
                title: $this->fallbackTitle($named),
                description: '',
                canonical: url()->current(),
                robots: $noindex ? 'noindex, follow' : 'index, follow',
            ),
        ]);
    }

    private function fallbackTitle(?string $named): string
    {
        return match ($named) {
            'foundations' => __('ui.foundations.title').' — KARAKO',
            'credits' => __('credits.title').' — KARAKO',
            null => __('ui.missing.title').' — KARAKO',
            default => 'KARAKO',
        };
    }
}
