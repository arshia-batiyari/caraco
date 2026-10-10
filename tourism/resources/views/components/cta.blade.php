@props(['eyebrow' => null, 'title', 'text' => null, 'href', 'label'])

<section class="k-cta">
    <div class="k-wrap">
        @if ($eyebrow)
            <p class="k-eyebrow">{{ $eyebrow }}</p>
        @endif
        <h2>{{ $title }}</h2>
        @if ($text)
            <p class="k-lede">{{ $text }}</p>
        @endif
        <div class="k-actions">
            <a class="k-btn k-btn-gold" href="{{ $href }}">{{ $label }}</a>
        </div>
    </div>
</section>
