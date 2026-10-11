@php
    $line = config('karako.site') === 'tourism'
        ? __('ui.brand.tourism')
        : __('ui.brand.holding');
@endphp

<a class="k-brand" href="{{ route('home') }}">
    <span class="k-logo-plate"><x-logo /></span>
    <span class="k-brand-line">{{ $line }}</span>
</a>
