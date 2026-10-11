@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('ui.nav.divisions'), 'url' => route('divisions.index')],
        ['label' => $translation->title],
    ]" />

    <section class="k-section">
        <div class="k-wrap k-narrow">
            <p class="k-index-no">{{ sprintf('%02d', $division->position) }}</p>
            <x-section-heading level="1" :title="$translation->title" />
            @if ($image)
                <img class="k-detail-image" src="{{ $image }}" alt="{{ $translation->title }}" decoding="async">
            @endif
            @if (filled($translation->introduction))
                <p class="k-lede">{{ $translation->introduction }}</p>
            @endif
            <x-prose-body :body="$translation->description" />
            @php $external = $division->externalUrl(); @endphp
            @if ($external)
                <div class="k-actions">
                    <a class="k-btn k-btn-ink" href="{{ $external }}" rel="noopener noreferrer">
                        {{ $division->key === 'tourism' ? __('ui.related.tourism') : __('ui.external') }}
                    </a>
                </div>
            @endif
        </div>
    </section>

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
