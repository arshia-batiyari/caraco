@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section">
        <div class="k-wrap">
            <header class="k-attraction-title">
                @if ($record->showsUnesco())
                    <p class="k-unesco">{{ __('ui.attractions.unesco') }}</p>
                @endif
                <h1>{{ $translation->title }}</h1>
                @if ($place['city'] || $place['country'])
                    <p class="k-lede">{{ collect([$place['city'], $place['country']])->filter()->implode(' · ') }}</p>
                @endif
                @if (filled($record->classification))
                    <p>{{ __('ui.attractions.classification') }}: {{ __('ui.attractions.classifications.'.$record->classification) }}</p>
                @endif
                @if (filled($translation->summary))
                    <p>{{ $translation->summary }}</p>
                @endif
            </header>
            @if ($cover)
                <figure class="k-attraction-cover">
                    <img src="{{ $cover }}" alt="{{ filled($translation->cover_alt) ? $translation->cover_alt : $translation->title }}">
                </figure>
            @endif
            <div class="k-narrow">
                @foreach ($paragraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
                @if ($record->showsUnesco() && ($record->inscribed_on || filled($record->unesco_reference)))
                    <h2>{{ __('ui.attractions.unesco') }}</h2>
                    @if ($record->inscribed_on)
                        <p>{{ __('ui.attractions.inscribed') }}: {{ $record->inscribed_on->toDateString() }}</p>
                    @endif
                    @if (filled($record->unesco_reference))
                        <p>{{ __('ui.attractions.reference') }}: {{ $record->unesco_reference }}</p>
                    @endif
                @endif
                @if ($visitor !== [])
                    <h2>{{ __('ui.attractions.visitor') }}</h2>
                    @foreach ($visitor as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endif
                @if ($record->latitude !== null && $record->longitude !== null)
                    <h2>{{ __('ui.attractions.location') }}</h2>
                    <svg class="k-route-svg" viewBox="0 0 640 220" role="img">
                        <rect width="640" height="220" fill="#f4efe6"></rect>
                        <circle cx="320" cy="110" r="6" fill="#141210"></circle>
                    </svg>
                    <p>{{ __('ui.attractions.coordinates') }}: {{ $record->latitude }}, {{ $record->longitude }}</p>
                @endif
            </div>
            @if ($record->images->isNotEmpty())
                <h2>{{ __('ui.attractions.gallery') }}</h2>
                <div class="k-media-grid">
                    @foreach ($record->images as $image)
                        <figure>
                            <img src="{{ \App\Support\ImageStore::url($image->path) }}" alt="{{ $image->altFor($translation->title) }}" loading="lazy" decoding="async">
                            @if ($image->caption())<figcaption>{{ $image->caption() }}</figcaption>@endif
                        </figure>
                    @endforeach
                </div>
            @endif
            <div class="k-narrow">
                @if ($place['destination']?->publicUrl('destinations.show'))
                    <h2>{{ __('ui.attractions.related_place') }}</h2>
                    <p><a href="{{ $place['destination']->publicUrl('destinations.show') }}">{{ $place['destination']->publicTranslation()->name }}</a></p>
                @endif
                @php $tours = $record->tours->filter(fn ($tour) => $tour->is_published && $tour->publicUrl('tours.show')); @endphp
                @if ($tours->isNotEmpty())
                    <h2>{{ __('ui.attractions.related_tours') }}</h2>
                    <ul class="k-inline-links">
                        @foreach ($tours as $tour)
                            <li><a href="{{ $tour->publicUrl('tours.show') }}">{{ $tour->publicTranslation()->title }}</a></li>
                        @endforeach
                    </ul>
                @endif
                @if ($record->category?->is_published && $record->category->publicTranslation())
                    <p>{{ __('ui.attractions.category') }}: {{ $record->category->publicTranslation()->name }}</p>
                @endif
            </div>
        </div>
    </article>
@endsection
