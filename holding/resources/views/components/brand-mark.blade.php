@php
    $line = config('karako.site') === 'tourism'
        ? __('ui.brand.tourism')
        : __('ui.brand.holding');
@endphp

<a class="k-brand" href="{{ route('home') }}">
    <span class="k-brand-name" translate="no">KARAKO</span>
    <span class="k-brand-line">{{ $line }}</span>
</a>
