@php
    $line = config('karako.site') === 'tourism'
        ? __('ui.brand.tourism')
        : __('ui.brand.holding');
@endphp

<a class="k-brand" href="{{ route('home') }}">
    <x-logo />
    <span class="k-sr">{{ $line }}</span>
</a>
