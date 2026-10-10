@props([
    'eyebrow' => null,
    'title',
    'lede' => null,
    'image' => null,
    'imageAlt' => '',
    'badge' => null,
    'variant' => 'editorial',
])

<section {{ $attributes->class(['k-hero', 'k-hero-'.$variant]) }}>
    @if ($image && $variant === 'immersive')
        <x-photo :name="$image" :alt="$imageAlt" variant="bleed" priority />
    @endif
    <div class="k-hero-copy">
        @if ($badge)
            <x-badge>{{ $badge }}</x-badge>
        @endif
        @if ($eyebrow)
            <p class="k-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h1>{{ $title }}</h1>
        @if ($lede)
            <p class="k-lede">{{ $lede }}</p>
        @endif
        @if (isset($actions) && ! $actions->isEmpty())
            <div class="k-actions">{{ $actions }}</div>
        @endif
    </div>
    @if ($image && $variant !== 'immersive')
        <x-photo :name="$image" :alt="$imageAlt" variant="bleed" priority />
    @endif
</section>
