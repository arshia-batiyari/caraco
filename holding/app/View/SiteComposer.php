<?php

namespace App\View;

use App\Models\ProfileTranslation;
use App\Support\LocaleUrl;
use App\Support\SeoData;
use App\Support\SiteContext;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
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

        $footerSummary = null;
        $navLinks = [];
        $footerLinks = [];
        $socialLinks = new Collection;
        $contactDetails = null;
        $contactEmail = null;
        $contactPhone = null;
        try {
            $footerSummary = ProfileTranslation::forLocale()?->summary;
            $navLinks = $this->site->links('header');
            $footerLinks = $this->site->links('footer');
            $socialLinks = $this->site->socialLinks();
            $contactDetails = $this->site->contact();
            $contactEmail = $this->site->email();
            $contactPhone = $this->site->phone();
        } catch (QueryException $exception) {
            if (! $this->missingTable($exception)) {
                throw $exception;
            }
        }

        $view->with([
            'brandLine' => __('ui.brand.holding'),
            'footerSummary' => $footerSummary,
            'navLinks' => $navLinks,
            'footerLinks' => $footerLinks,
            'socialLinks' => $socialLinks,
            'contactDetails' => $contactDetails,
            'contactEmail' => $contactEmail,
            'contactPhone' => $contactPhone,
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

    private function missingTable(QueryException $exception): bool
    {
        $state = (string) ($exception->errorInfo[0] ?? '');

        return $state === '42S02' || str_contains($exception->getMessage(), 'no such table');
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
