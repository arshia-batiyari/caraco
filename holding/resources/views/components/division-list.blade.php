@props(['divisions', 'heading' => null, 'level' => 2])

@php
    $tourism = $divisions->firstWhere('key', 'tourism');
    $tourismImage = $tourism ? \App\Support\PreviewMedia::division($tourism->image_path, $tourism->key) : null;
@endphp

<section class="bx-band">
    <div class="bx-shell">
        @if ($heading)
            <div class="bx-band-head bx-rise">
                @if ((int) $level === 1)
                    <h1>{{ $heading }}</h1>
                @else
                    <h2>{{ $heading }}</h2>
                @endif
            </div>
        @endif

        @if ($tourism && $tourismImage)
            @php
                $translation = $tourism->publicTranslation();
                $detailUrl = $tourism->publicUrl('divisions.show');
                $plateUrl = $detailUrl ?: $tourism->externalUrl();
            @endphp
            <a class="bx-feature bx-rise" @if ($plateUrl) href="{{ $plateUrl }}" @endif @if (! $detailUrl && $plateUrl) rel="noopener noreferrer" @endif>
                <img src="{{ $tourismImage }}" alt="" loading="lazy" decoding="async">
                <span>
                    <span class="bx-card-no">{{ sprintf('%02d', $tourism->position) }}</span>
                    <span class="bx-card-title">{{ $translation->title }}</span>
                </span>
            </a>
        @endif

        <div class="bx-cards">
            @foreach ($divisions as $division)
                @continue($tourismImage && $division->key === 'tourism')
                @php
                    $translation = $division->publicTranslation();
                    $detailUrl = $division->publicUrl('divisions.show');
                    $image = \App\Support\PreviewMedia::division($division->image_path, $division->key);
                @endphp
                <{{ $detailUrl ? 'a' : 'div' }} class="bx-card bx-rise" @if ($detailUrl) href="{{ $detailUrl }}" @endif style="--bx-d: {{ min($loop->index, 8) * 60 }}ms">
                    @if ($image)
                        <img src="{{ $image }}" alt="" loading="lazy" decoding="async">
                    @endif
                    <span class="bx-card-top">
                        <span class="bx-card-no">{{ sprintf('%02d', $division->position) }}</span>
                        <span class="bx-card-go" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="16" height="16"><path d="M7 17 17 7M9 7h8v8" fill="none" stroke="currentColor" stroke-width="1.6"></path></svg>
                        </span>
                    </span>
                    <span class="bx-card-title">{{ $translation->title }}</span>
                    @if (filled($translation->introduction))
                        <span class="bx-card-text">{{ $translation->introduction }}</span>
                    @endif
                </{{ $detailUrl ? 'a' : 'div' }}>
            @endforeach
        </div>
    </div>
</section>
