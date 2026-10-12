@props(['eyebrow' => null, 'title', 'text' => null, 'href', 'label'])

<section class="k-next">
    <a class="k-next-link" href="{{ $href }}">
        <span class="k-next-copy">
            @if ($eyebrow)
                <span class="k-eyebrow">{{ $eyebrow }}</span>
            @endif
            <h2 class="k-next-title">{{ $title }}</h2>
            @if ($text)
                <span class="k-next-text">{{ $text }}</span>
            @endif
            <span class="k-next-label">{{ $label }}</span>
        </span>
        <span class="k-next-arrow" aria-hidden="true">
            <svg viewBox="0 0 120 120" fill="none">
                <path d="M8 60h88M68 28l36 32-36 32" stroke="currentColor" stroke-width="2"></path>
            </svg>
        </span>
    </a>
</section>
