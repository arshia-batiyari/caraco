@props([
    'eyebrow' => null,
    'title',
    'lede' => null,
    'image' => null,
    'imageUrl' => null,
    'imageAlt' => '',
    'badge' => null,
    'variant' => 'editorial',
    'lang' => null,
])

<section {{ $attributes->class(['k-hero', 'k-hero-'.$variant]) }}>
    @if ($imageUrl && $variant === 'immersive')
        <figure class="k-photo k-photo-bleed">
            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" fetchpriority="high" decoding="async">
        </figure>
    @elseif ($image && $variant === 'immersive')
        <x-photo :name="$image" :alt="$imageAlt" variant="bleed" priority />
    @endif
    <div class="k-hero-copy">
        @if ($badge)
            <x-badge>{{ $badge }}</x-badge>
        @endif
        @if ($eyebrow)
            <p class="k-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1 @if (filled($lang)) lang="{{ $lang }}" @endif>{{ $title }}</h1>
        @if ($lede)
            <p class="k-lede">{{ $lede }}</p>
        @endif
        @if (isset($actions) && ! $actions->isEmpty())
            <div class="k-actions">{{ $actions }}</div>
        @endif
    </div>
    @if ($imageUrl && $variant !== 'immersive')
        <figure class="k-photo k-photo-bleed">
            <img src="{{ $imageUrl }}" alt="{{ $imageAlt }}" fetchpriority="high" decoding="async">
        </figure>
    @elseif ($image && $variant !== 'immersive')
        <x-photo :name="$image" :alt="$imageAlt" variant="bleed" priority />
    @endif
</section>
