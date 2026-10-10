@props(['title', 'text' => null, 'level' => 2])

<div {{ $attributes->class(['k-empty']) }}>
    <p class="k-eyebrow">{{ __('ui.empty_kicker') }}</p>
    @if ((int) $level === 1)
        <h1>{{ $title }}</h1>
    @else
        <h2>{{ $title }}</h2>
    @endif
    @if ($text)
        <p>{{ $text }}</p>
    @endif
    @if (! $slot->isEmpty())
        <div class="k-actions">{{ $slot }}</div>
    @endif
</div>
