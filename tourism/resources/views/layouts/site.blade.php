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
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('brand/favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
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
    @foreach (config('karako.locales') as $code => $meta)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $alternates[$code] ?? \App\Support\LocaleUrl::forLocale($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternates['fa'] ?? url('/fa') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <noscript>
        <style>
            @media (max-width: 959px) {
                .k-panel { position: static !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important; }
            }
            .k-locale-panel { display: block !important; }
        </style>
    </noscript>
</head>
<body>
    <a class="k-skip" href="#content">{{ __('ui.skip') }}</a>
    <header class="k-header {{ trim($__env->yieldContent('header_tone')) }}" data-nav>
        <div class="k-header-bar k-wrap-wide">
            <x-brand-mark />
            <button type="button" class="k-menu-toggle" data-menu-toggle aria-expanded="false" aria-controls="primary-menu">
                <span class="k-menu-icon" aria-hidden="true"></span>
                <span class="k-sr">{{ __('ui.menu') }}</span>
            </button>
            <div class="k-panel" id="primary-menu" data-menu-panel>
                <nav class="k-nav" aria-label="{{ __('ui.primary_nav') }}">
                    @foreach ($navLinks as $link)
                        <a href="{{ $link['url'] }}" @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    @endforeach
                </nav>
                <a class="k-btn k-btn-gold" href="{{ route('consultation') }}">{{ __('ui.nav.consultation') }}</a>
                <x-language-switcher :alternates="$alternates" />
            </div>
        </div>
    </header>

    <main id="content" tabindex="-1">
        @yield('content')
    </main>

    <footer class="k-footer">
        <div class="k-wrap k-footer-grid">
            <div>
                <a class="k-footer-brand" href="{{ route('home') }}"><x-logo size="footer" /></a>
            </div>
            <div>
                <h2>{{ __('ui.footer.navigate') }}</h2>
                <ul>
                    @foreach ($navLinks as $link)
                        <li><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                    @endforeach
                    @if ($relatedUrl)
                        <li><a href="{{ $relatedUrl }}" rel="noopener noreferrer">{{ $relatedLabel }}</a></li>
                    @endif
                </ul>
            </div>
            @if ($contactEmail || $contactPhone)
                <div>
                    <h2>{{ __('ui.footer.contact') }}</h2>
                    <ul>
                        @if ($contactEmail)
                            <li><a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a></li>
                        @endif
                        @if ($contactPhone)
                            <li><a href="tel:{{ preg_replace('/\s+/', '', $contactPhone) }}">{{ $contactPhone }}</a></li>
                        @endif
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
