@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('ui.nav.projects'), 'url' => route('projects.index')],
        ['label' => $translation->title],
    ]" />

    <section class="k-section">
        <div class="k-wrap k-narrow">
            <x-section-heading level="1" :title="$translation->title" />
            @if ($image)
                <img class="k-detail-image" src="{{ $image }}" alt="{{ $translation->title }}" decoding="async">
            @endif
            @if (filled($translation->summary))
                <p class="k-lede">{{ $translation->summary }}</p>
            @endif
            <x-prose-body :body="$translation->body" />
        </div>
    </section>

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
