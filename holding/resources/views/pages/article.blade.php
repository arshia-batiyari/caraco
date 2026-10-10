@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="$crumbs" />

    <section class="k-section">
        <div class="k-wrap k-narrow">
            <x-section-heading level="1" :title="$title" />
            <x-prose-body :body="$body" />
        </div>
    </section>

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
