@props(['name', 'alt', 'caption' => null, 'variant' => 'frame', 'priority' => false])

@php
    $image = config('sample-images.'.$name);
@endphp

<figure {{ $attributes->class(['k-photo', 'k-photo-'.$variant]) }}>
    <img
        src="{{ asset('images/'.$image['file']) }}"
        alt="{{ $alt }}"
        @if ($priority) fetchpriority="high" @else loading="lazy" @endif
        decoding="async"
    >
    <figcaption>
        @if ($caption)
            <span>{{ $caption }}</span>
        @endif
        <a class="k-credit" href="{{ $image['source'] }}" rel="noopener noreferrer">{{ $image['author'] }} · {{ $image['license'] }}</a>
    </figcaption>
</figure>
