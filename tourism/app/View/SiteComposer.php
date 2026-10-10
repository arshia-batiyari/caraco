<?php

namespace App\View;

use Illuminate\View\View;

class SiteComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'brandLine' => __('ui.brand.tourism'),
            'metaDescription' => __('ui.meta.tourism'),
            'footerBlurb' => __('ui.footer.tourism'),
            'navLinks' => [
                ['route' => 'home', 'label' => __('ui.nav.home')],
                ['route' => 'journey', 'label' => __('ui.nav.journey')],
                ['route' => 'inquiry', 'label' => __('ui.nav.inquiry')],
            ],
            'utilityLinks' => [
                ['route' => 'foundations', 'label' => __('ui.nav.foundations')],
                ['route' => 'credits', 'label' => __('ui.nav.credits')],
            ],
            'relatedUrl' => config('karako.related_url') ?: null,
            'relatedLabel' => __('ui.related.holding'),
        ]);
    }
}
