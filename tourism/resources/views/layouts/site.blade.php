<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('karako.locales.'.app()->getLocale().'.dir', 'rtl') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) — KARAKO</title>
    <meta name="description" content="@yield('description', $metaDescription ?? '')">
    @foreach (config('karako.locales') as $code => $meta)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ \App\Support\LocaleUrl::forLocale($code) }}">
    @endforeach
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            <div class="k-panel" id="primary-menu" data-menu-panel>
                <nav class="k-nav" aria-label="{{ __('ui.primary_nav') }}">
                    @foreach ($navLinks as $link)
                        <a href="{{ route($link['route']) }}" @if (request()->routeIs($link['route'])) aria-current="page" @endif>
                            {{ $link['label'] }}
                        </a>
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
                <p>{{ $footerBlurb }}</p>
            </div>
            <div>
                <h2>{{ __('ui.footer.navigate') }}</h2>
                <ul>
                    @foreach ($navLinks as $link)
                        <li><a href="{{ route($link['route']) }}">{{ $link['label'] }}</a></li>
                    @endforeach
                    @if ($relatedUrl)
                        <li><a href="{{ $relatedUrl }}">{{ $relatedLabel }}</a></li>
                    @endif
                </ul>
            </div>
            <div>
                <h2>{{ __('ui.footer.samples') }}</h2>
                <ul>
                    @foreach ($utilityLinks as $link)
                        <li><a href="{{ route($link['route']) }}">{{ $link['label'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="k-wrap k-footer-base">
            <p>© {{ now()->year }} KARAKO</p>
            <p>{{ __('ui.footer.sample_note') }}</p>
        </div>
    </footer>
</body>
</html>
