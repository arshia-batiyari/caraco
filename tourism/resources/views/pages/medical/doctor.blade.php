@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section">
        <div class="k-wrap k-narrow">
            <p class="k-eyebrow">{{ __('ui.medical.doctors') }}</p>
            <h1>{{ $translation->name }}</h1>
            @if (filled($translation->specialty))
                <p class="k-lede">{{ $translation->specialty }}</p>
            @endif
            @if ($record->provider?->is_published && $record->provider->publicUrl('medical.providers.show'))
                <p><a href="{{ $record->provider->publicUrl('medical.providers.show') }}">{{ $record->provider->publicTranslation()->name }}</a></p>
            @endif
            <p class="k-notice">{{ __('ui.medical.profile_note') }}</p>
            @if ($image)
                <figure class="k-attraction-cover">
                    <img src="{{ $image }}" alt="{{ filled($translation->image_alt) ? $translation->image_alt : $translation->name }}">
                </figure>
            @endif
            @foreach (\App\Support\LocaleText::paragraphs($translation->biography) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>
@endsection
