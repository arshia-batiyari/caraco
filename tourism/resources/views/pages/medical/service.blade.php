@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section">
        <div class="k-wrap">
            <header class="k-narrow">
                <p class="k-eyebrow">{{ __('ui.medical.title') }}</p>
                <h1>{{ $translation->name }}</h1>
                @if ($record->category?->is_published && $record->category->publicTranslation())
                    <p><a href="{{ $record->category->publicUrl('medical.categories.show') }}">{{ $record->category->publicTranslation()->name }}</a></p>
                @endif
                @if (filled($translation->summary))<p class="k-lede">{{ $translation->summary }}</p>@endif
                <p class="k-notice">{{ __('ui.medical.disclaimer') }}</p>
            </header>
            @if ($cover)
                <figure class="k-attraction-cover">
                    <img src="{{ $cover }}" alt="{{ filled($translation->cover_alt) ? $translation->cover_alt : $translation->name }}">
                </figure>
            @endif
            <div class="k-narrow">
                @foreach (\App\Support\LocaleText::paragraphs($translation->description) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
            @if ($record->images->isNotEmpty())
                <h2>{{ __('ui.medical.gallery') }}</h2>
                <div class="k-media-grid">
                    @foreach ($record->images as $image)
                        <figure>
                            <img src="{{ \App\Support\ImageStore::url($image->path) }}" alt="{{ $image->altFor($translation->name) }}" loading="lazy" decoding="async">
                            @if ($image->caption())<figcaption>{{ $image->caption() }}</figcaption>@endif
                        </figure>
                    @endforeach
                </div>
            @endif
            @php $clinics = $record->providers->filter(fn ($provider) => $provider->is_published && $provider->publicUrl('medical.providers.show')); @endphp
            @if ($clinics->isNotEmpty())
                <h2>{{ __('ui.medical.clinics') }}</h2>
                <ul class="k-inline-links">
                    @foreach ($clinics as $clinic)
                        <li><a href="{{ $clinic->publicUrl('medical.providers.show') }}">{{ $clinic->publicTranslation()->name }}</a></li>
                    @endforeach
                </ul>
            @endif
            <div class="k-narrow" id="consultation">
                <h2>{{ __('ui.medical.submit') }}</h2>
                @include('partials.medical-inquiry-form', ['services' => $inquiryServices, 'privacy' => $privacy, 'selected' => $record->id])
            </div>
        </div>
    </article>
@endsection
