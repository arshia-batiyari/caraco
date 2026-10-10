<?php

namespace App\View;

use Illuminate\View\View;

class SiteComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'brandLine' => __('ui.brand.holding'),
            'metaDescription' => __('ui.meta.holding'),
            'footerBlurb' => __('ui.footer.holding'),
            'navLinks' => [
                ['route' => 'home', 'label' => __('ui.nav.home')],
                ['route' => 'divisions', 'label' => __('ui.nav.divisions')],
                ['route' => 'contact', 'label' => __('ui.nav.contact')],
            ],
            'utilityLinks' => [
                ['route' => 'foundations', 'label' => __('ui.nav.foundations')],
                ['route' => 'credits', 'label' => __('ui.nav.credits')],
            ],
            'relatedUrl' => config('karako.related_url') ?: null,
            'relatedLabel' => __('ui.related.tourism'),
        ]);
    }
}
