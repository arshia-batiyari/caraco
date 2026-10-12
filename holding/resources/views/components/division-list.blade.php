@props(['divisions', 'heading' => null, 'level' => 2])

@php
    $tourism = $divisions->firstWhere('key', 'tourism');
    $tourismImage = $tourism ? \App\Support\ImageStore::url($tourism->image_path) : null;
    $rows = $tourismImage ? $divisions->reject(fn ($division) => $division->key === 'tourism') : $divisions;
@endphp

<section class="k-section k-index-section">
    @if ($heading)
        <div class="k-wrap">
            <x-section-heading :title="$heading" :level="$level" />
        </div>
    @endif

    @if ($tourism && $tourismImage)
        @php
            $translation = $tourism->publicTranslation();
            $detailUrl = $tourism->publicUrl('divisions.show');
            $plateUrl = $detailUrl ?: $tourism->externalUrl();
        @endphp
        <{{ $plateUrl ? 'a' : 'div' }} class="k-plate" @if ($plateUrl) href="{{ $plateUrl }}" @endif @if (! $detailUrl && $plateUrl) rel="noopener noreferrer" @endif>
            <img src="{{ $tourismImage }}" alt="{{ $translation->title }}" loading="lazy" decoding="async">
            <span class="k-plate-copy">
                <span class="k-row-no">{{ sprintf('%02d', $tourism->position) }}</span>
                <span class="k-row-title">{{ $translation->title }}</span>
                @if (filled($translation->introduction))
                    <span class="k-row-text">{{ $translation->introduction }}</span>
                @endif
            </span>
        </{{ $plateUrl ? 'a' : 'div' }}>
    @endif

    @if ($rows->isNotEmpty())
        <div class="k-rows">
            @foreach ($rows as $division)
                @php
                    $translation = $division->publicTranslation();
                    $detailUrl = $division->publicUrl('divisions.show');
                @endphp
                <{{ $detailUrl ? 'a' : 'div' }} class="k-row" @if ($detailUrl) href="{{ $detailUrl }}" @endif>
                    <span class="k-row-no">{{ sprintf('%02d', $division->position) }}</span>
                    <span class="k-row-main">
                        <span class="k-row-title">{{ $translation->title }}</span>
                        @if (filled($translation->introduction))
                            <span class="k-row-text">{{ $translation->introduction }}</span>
                        @endif
                    </span>
                    <span class="k-row-go" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M5 12h14M13 6l6 6-6 6"></path>
                        </svg>
                    </span>
                </{{ $detailUrl ? 'a' : 'div' }}>
            @endforeach
        </div>
    @endif
</section>
