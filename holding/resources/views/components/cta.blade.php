@props(['eyebrow' => null, 'title', 'text' => null, 'href', 'label'])

<section class="bx-close">
    <div class="bx-shell bx-close-panel bx-rise">
        @if ($eyebrow)
            <p class="bx-kicker">{{ $eyebrow }}</p>
        @endif
        <h2>{{ $title }}</h2>
        @if ($text)
            <p>{{ $text }}</p>
        @endif
        <a class="bx-pill" href="{{ $href }}">{{ $label }}</a>
    </div>
</section>
