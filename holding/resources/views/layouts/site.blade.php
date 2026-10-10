@php
    $seoTitle = $seo->title ?? 'KARAKO';
    $seoDescription = $seo->description ?? '';
    $seoCanonical = $seo->canonical ?: url()->current();
    $seoImage = $seo->image ?? null;
    $seoRobots = $seo->robots ?? 'index, follow';
    $localeMap = ['fa' => 'fa_IR', 'en' => 'en_US', 'ru' => 'ru_RU', 'ar' => 'ar_AR'];
    $ogLocale = $localeMap[app()->getLocale()] ?? app()->getLocale();
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('karako.locales.'.app()->getLocale().'.dir', 'rtl') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle }}</title>
    @if ($seoDescription !== '')
        <meta name="description" content="{{ $seoDescription }}">
    @endif
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    @if ($seoDescription !== '')
        <meta property="og:description" content="{{ $seoDescription }}">
    @endif
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="KARAKO">
    <meta property="og:locale" content="{{ $ogLocale }}">
    @if ($seoImage)
        <meta property="og:image" content="{{ $seoImage }}">
    @endif
    <meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    @if ($seoDescription !== '')
        <meta name="twitter:description" content="{{ $seoDescription }}">
    @endif
    @foreach (config('karako.locales') as $code => $meta)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $alternates[$code] ?? \App\Support\LocaleUrl::forLocale($code) }}">
        @if ($code !== app()->getLocale())
            <meta property="og:locale:alternate" content="{{ $localeMap[$code] ?? $code }}">
        @endif
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternates['fa'] ?? url('/fa') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <noscript>
        <style>
            @media (max-width: 959px) {
                .k-panel { position: static !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important; }
            }
        </style>
    </noscript>
</head>
<body>
    <a class="k-skip" href="#content">{{ __('ui.skip') }}</a>
    <header class="k-header {{ trim($__env->yieldContent('header_tone')) }}" data-nav>
        <div class="k-header-bar k-wrap-wide">
            <x-brand-mark />
            <div class="k-header-tools">
                <a class="k-btn k-btn-gold k-header-cta k-header-cta-bar" href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
                <button
                    type="button"
                    class="k-menu-toggle"
                    data-menu-toggle
                    aria-expanded="false"
                    aria-controls="primary-menu"
                >
                    <span class="k-menu-icon" aria-hidden="true"></span>
                    <span class="k-sr">{{ __('ui.menu') }}</span>
                </button>
            </div>
            <div class="k-panel" id="primary-menu" data-menu-panel>
                <a class="k-btn k-btn-gold k-header-cta k-header-cta-panel" href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
                <nav class="k-nav" aria-label="{{ __('ui.primary_nav') }}">
                    @foreach ($navLinks as $link)
                        <a
                            href="{{ $link['url'] }}"
                            @if ($link['active']) aria-current="page" @endif
                            @if ($link['external']) rel="noopener noreferrer" target="_blank" @endif
                        >{{ $link['label'] }}</a>
                    @endforeach
                </nav>
                <x-language-switcher />
            </div>
        </div>
    </header>

    <main id="content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="k-footer">
        <div class="k-wrap k-footer-grid">
            <div>
                <p class="k-footer-brand" translate="no">KARAKO</p>
                @if (filled($footerSummary))
                    <p>{{ $footerSummary }}</p>
                @endif
            </div>
            @if ($footerLinks || $relatedUrl)
                <div>
                    <h2>{{ __('ui.footer.navigate') }}</h2>
                    <ul>
                        @foreach ($footerLinks as $link)
                            <li>
                                <a href="{{ $link['url'] }}" @if ($link['external']) rel="noopener noreferrer" target="_blank" @endif>{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                        @if ($relatedUrl)
                            <li><a href="{{ $relatedUrl }}" rel="noopener noreferrer">{{ $relatedLabel }}</a></li>
                        @endif
                    </ul>
                </div>
            @endif
            @if ($contactEmail || $contactPhone || filled($contactDetails?->address) || filled($contactDetails?->hours))
                <div>
                    <h2>{{ __('ui.footer.contact') }}</h2>
                    <ul>
                        @if ($contactEmail)
                            <li><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
                        @endif
                        @if ($contactPhone)
                            <li><a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}">{{ $contactPhone }}</a></li>
                        @endif
                        @if (filled($contactDetails?->address))
                            <li>{{ $contactDetails->address }}</li>
                        @endif
                        @if (filled($contactDetails?->hours))
                            <li>{{ $contactDetails->hours }}</li>
                        @endif
                    </ul>
                </div>
            @endif
            @if ($socialLinks->isNotEmpty())
                <div>
                    <h2>{{ __('ui.footer.follow') }}</h2>
                    <ul>
                        @foreach ($socialLinks as $social)
                            <li><a href="{{ $social->url }}" rel="noopener noreferrer">{{ __('ui.social.'.$social->platform) }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="k-wrap k-footer-base">
            <p>© {{ now()->year }} KARAKO</p>
        </div>
    </footer>
</body>
</html>
