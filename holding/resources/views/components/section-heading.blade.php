@props(['eyebrow' => null, 'title', 'intro' => null, 'level' => 2])

<div {{ $attributes->class(['k-section-head']) }}>
    @if ($eyebrow)
        <p class="k-eyebrow">{{ $eyebrow }}</p>
    @endif
    @if ((int) $level === 1)
        <h1>{{ $title }}</h1>
    @else
        <h2>{{ $title }}</h2>
    @endif
    @if ($intro)
        <p>{{ $intro }}</p>
    @endif
</div>
