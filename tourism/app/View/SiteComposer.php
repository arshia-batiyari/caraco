<?php

namespace App\View;

use App\Models\Setting;
use App\Support\LocaleUrl;
use App\Support\SeoData;
use Illuminate\Database\QueryException;
use Illuminate\View\View;

class SiteComposer
{
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

        $contactEmail = null;
        $contactPhone = null;
        try {
            $contactEmail = Setting::getValue('contact_email');
            $contactPhone = Setting::getValue('contact_phone');
        } catch (QueryException $exception) {
            if (! $this->missingTable($exception)) {
                throw $exception;
            }
        }

        $named = request()->route()?->getName();
        $noindex = $named === null || in_array($named, ['foundations', 'credits'], true);
        $existing = $view->getData();
        $home = $named === 'home';

        $view->with([
            'brandLine' => __('ui.brand.tourism'),
            'navLinks' => [
                ['url' => route('home'), 'label' => __('ui.nav.home'), 'active' => $home, 'external' => false],
                ['url' => route('tours.index'), 'label' => __('ui.nav.tours'), 'active' => request()->routeIs('tours.*'), 'external' => false],
                ['url' => route('attractions.index'), 'label' => __('ui.nav.attractions'), 'active' => request()->routeIs('attractions.*'), 'external' => false],
                ['url' => route('medical.index'), 'label' => __('ui.nav.medical'), 'active' => request()->routeIs('medical.*'), 'external' => false],
                ['url' => route('feedback.index'), 'label' => __('ui.nav.feedback'), 'active' => request()->routeIs('feedback.*'), 'external' => false],
                ['url' => route('consultation'), 'label' => __('ui.nav.consultation'), 'active' => request()->routeIs('consultation'), 'external' => false],
            ],
            'contactEmail' => $contactEmail,
            'contactPhone' => $contactPhone,
            'relatedUrl' => config('karako.related_url') ?: null,
            'relatedLabel' => __('ui.related.holding'),
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
