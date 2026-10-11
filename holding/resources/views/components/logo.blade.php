@props(['size' => 'header'])

<img
    {{ $attributes->merge(['class' => 'k-logo k-logo-'.$size]) }}
    src="{{ asset('brand/logo.png') }}"
    alt="CARACO"
    width="720"
    height="393"
    decoding="async"
>
