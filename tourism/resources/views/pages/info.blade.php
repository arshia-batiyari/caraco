@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section">
        <div class="k-wrap k-narrow">
            <h1>{{ $translation->title }}</h1>
            @foreach (\App\Support\LocaleText::paragraphs($translation->body) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>
@endsection
