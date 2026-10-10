<nav class="k-langs" aria-label="{{ __('ui.language') }}">
    <p class="k-langs-label" aria-hidden="true">{{ __('ui.language') }}</p>
    <ul>
        @foreach (config('karako.locales') as $code => $meta)
            <li>
                <a
                    href="{{ \App\Support\LocaleUrl::forLocale($code) }}"
                    hreflang="{{ $code }}"
                    lang="{{ $code }}"
                    @if (app()->getLocale() === $code) aria-current="true" @endif
                >{{ $meta['native'] }}</a>
            </li>
        @endforeach
    </ul>
</nav>
