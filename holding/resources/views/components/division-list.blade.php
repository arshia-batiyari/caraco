@props(['divisions', 'heading' => null])

@php
    $tourism = $divisions->firstWhere('key', 'tourism');
    $rest = $divisions->reject(fn ($division) => $division->key === 'tourism');
@endphp

<section class="k-section">
    <div class="k-wrap">
        @if ($heading)
            <x-section-heading :title="$heading" />
        @endif

        @if ($tourism)
            @php
                $translation = $tourism->publicTranslation();
                $detailUrl = $tourism->publicUrl('divisions.show');
                $external = $tourism->externalUrl();
                $image = \App\Support\ImageStore::url($tourism->image_path);
            @endphp
            <article class="k-feature k-feature-tourism">
                <div class="k-feature-copy">
                    <p class="k-index-no">{{ sprintf('%02d', $tourism->position) }}</p>
                    <h3 class="k-index-title">
                        @if ($detailUrl)
                            <a href="{{ $detailUrl }}">{{ $translation->title }}</a>
                        @else
                            {{ $translation->title }}
                        @endif
                    </h3>
                    @if (filled($translation->introduction))
                        <p>{{ $translation->introduction }}</p>
                    @endif
                    @if ($external)
                        <div class="k-actions">
                            <a class="k-btn k-btn-gold" href="{{ $external }}" rel="noopener noreferrer">{{ __('ui.related.tourism') }}</a>
                        </div>
                    @endif
                </div>
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $translation->title }}" loading="lazy" decoding="async">
                @endif
            </article>
        @endif

        @if ($rest->isNotEmpty())
            <div class="k-index">
                @foreach ($rest as $division)
                    @php
                        $translation = $division->publicTranslation();
                        $detailUrl = $division->publicUrl('divisions.show');
                    @endphp
                    <a class="k-index-link" href="{{ $detailUrl }}">
                        <span class="k-index-no">{{ sprintf('%02d', $division->position) }}</span>
                        <span class="k-index-title">{{ $translation->title }}</span>
                        @if (filled($translation->introduction))
                            <span class="k-index-text">{{ $translation->introduction }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
