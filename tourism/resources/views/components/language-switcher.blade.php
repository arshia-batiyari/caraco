@props(['alternates' => null])

@php
    $locales = config('karako.locales');
    $current = app()->getLocale();
    $currentMeta = $locales[$current] ?? ['native' => strtoupper($current)];
    $alternateLinks = is_array($alternates) ? $alternates : request()->attributes->get('alternates');
@endphp

<div class="k-locale" data-locale>
    <button
        type="button"
        class="k-locale-toggle"
        data-locale-toggle
        aria-expanded="false"
        aria-controls="locale-menu"
        aria-haspopup="listbox"
    >
        <span class="k-sr">{{ __('ui.language') }}</span>
        <span class="k-locale-current" lang="{{ $current }}">{{ $currentMeta['native'] }}</span>
        <span class="k-locale-caret" aria-hidden="true"></span>
    </button>
    <ul id="locale-menu" class="k-locale-menu" data-locale-menu role="listbox" hidden>
        @foreach ($locales as $code => $meta)
            <li role="none">
                <a
                    role="option"
                    href="{{ is_array($alternateLinks) && isset($alternateLinks[$code]) ? $alternateLinks[$code] : \App\Support\LocaleUrl::forLocale($code) }}"
                    hreflang="{{ $code }}"
                    lang="{{ $code }}"
                    @if ($current === $code) aria-current="true" aria-selected="true" @endif
                >{{ $meta['native'] }}</a>
            </li>
        @endforeach
    </ul>
</div>
