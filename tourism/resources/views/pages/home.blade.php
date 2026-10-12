@extends('layouts.site')

@if ($heroImage)
    @section('header_tone', 'k-header-overlay')
@endif

@section('content')
    @if ($schema)
        <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @endif

    @if ($heroImage && filled($home?->hero_title))
        <x-hero variant="immersive" :eyebrow="$home->hero_eyebrow" :title="$home->hero_title" :lede="$home->hero_lede" :image-url="$heroImage" :image-alt="$home->hero_title">
            <x-slot:actions>
                <a class="k-btn k-btn-gold" href="{{ route('tours.index') }}">{{ __('ui.nav.tours') }}</a>
                <a class="k-btn k-btn-line" href="{{ route('consultation') }}">{{ __('ui.nav.consultation') }}</a>
            </x-slot:actions>
        </x-hero>
    @else
        <section class="k-masthead">
            <div class="k-wrap k-masthead-grid">
                <div>
                    @if (filled($home?->hero_eyebrow))
                        <p class="k-eyebrow">{{ $home->hero_eyebrow }}</p>
                    @endif
                    @if (filled($home?->hero_title))
                        <h1>{{ $home->hero_title }}</h1>
                    @else
                        <h1 class="k-wordmark"><x-logo size="masthead" /></h1>
                    @endif
                    @if (filled($home?->hero_lede))
                        <p class="k-lede">{{ $home->hero_lede }}</p>
                    @endif
                    <div class="k-actions">
                        <a class="k-btn k-btn-gold" href="{{ route('tours.index') }}">{{ __('ui.nav.tours') }}</a>
                        <a class="k-btn k-btn-line" href="{{ route('consultation') }}">{{ __('ui.nav.consultation') }}</a>
                    </div>
                </div>
                @if ($heroImage)
                    <img src="{{ $heroImage }}" alt="{{ filled($home?->hero_title) ? $home->hero_title : 'KARAKO' }}" fetchpriority="high" decoding="async">
                @endif
            </div>
        </section>
    @endif

    <section class="k-section k-section-tight k-discover-band">
        <div class="k-wrap">
            <h2>{{ __('ui.home.discover') }}</h2>
            <form class="k-discover" method="get" action="{{ route('tours.index') }}">
                <div class="k-field">
                    <label for="discover-scope">{{ __('ui.filters.scope') }}</label>
                    <select id="discover-scope" name="scope">
                        <option value="">{{ __('ui.fields.none') }}</option>
                        <option value="domestic">{{ __('ui.tours.domestic') }}</option>
                        <option value="international">{{ __('ui.tours.international') }}</option>
                    </select>
                </div>
                <div class="k-field">
                    <label for="discover-destination">{{ __('ui.filters.destination') }}</label>
                    <select id="discover-destination" name="destination">
                        <option value="">{{ __('ui.fields.none') }}</option>
                        @foreach ($filterOptions['destinations'] as $destination)
                            @if ($destination->publicTranslation())
                                <option value="{{ $destination->id }}">{{ $destination->publicTranslation()->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="discover-date">{{ __('ui.filters.depart_from') }}</label>
                    <input id="discover-date" name="depart_from" type="date">
                </div>
                <button class="k-btn k-btn-ink" type="submit">{{ __('ui.filters.apply') }}</button>
            </form>
        </div>
    </section>

    @if (filled($home?->intro_heading) || filled($home?->intro_body))
        <section class="k-section">
            <div class="k-wrap k-narrow">
                @if (filled($home?->intro_heading))
                    <x-section-heading :title="$home->intro_heading" />
                @endif
                @foreach (\App\Support\LocaleText::paragraphs($home?->intro_body) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </section>
    @endif

    @if ($destinations->isNotEmpty())
        <section class="k-section">
            <div class="k-wrap">
                <x-section-heading :title="$home?->destinations_heading ?? __('ui.home.destinations')" />
                <div class="k-editorial-rail">
                    @foreach ($destinations as $destination)
                        <a class="k-tour-card" href="{{ $destination->publicUrl('destinations.show') }}">
                            @if ($destination->image_path)
                                <img src="{{ \App\Support\ImageStore::url($destination->image_path) }}" alt="{{ $destination->publicTranslation()->name }}" loading="lazy" decoding="async">
                            @endif
                            <h3>{{ $destination->publicTranslation()->name }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section class="k-section">
            <div class="k-wrap">
                <x-section-heading :title="$home?->categories_heading ?? __('ui.home.categories')" />
                <ul class="k-chip-list">
                    @foreach ($categories as $category)
                        <li><a href="{{ $category->publicUrl('categories.show') }}">{{ $category->publicTranslation()->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @foreach ([
        ['items' => $popular, 'title' => $home?->popular_heading ?? __('ui.home.popular'), 'tone' => 'k-band-feature'],
        ['items' => $discounted, 'title' => $home?->discounted_heading ?? __('ui.home.discounted'), 'tone' => 'k-band-sale'],
        ['items' => $domestic, 'title' => $home?->domestic_heading ?? __('ui.home.domestic'), 'tone' => 'k-band-rail'],
        ['items' => $international, 'title' => $home?->international_heading ?? __('ui.home.international'), 'tone' => 'k-band-rail k-band-offset'],
    ] as $block)
        @if ($block['items']->isNotEmpty())
            <section class="k-section {{ $block['tone'] }}">
                <div class="k-wrap">
                    <x-section-heading :title="$block['title']" />
                    <div class="k-card-grid">
                        @foreach ($block['items'] as $tour)
                            @include('partials.tour-card', ['tour' => $tour])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endforeach

    @if ($hotels->isNotEmpty())
        <section class="k-section">
            <div class="k-wrap">
                <x-section-heading :title="$home?->experiences_heading ?? __('ui.home.experiences')" />
                <div class="k-stay-list">
                    @foreach ($hotels as $hotel)
                        <article class="k-tour-card">
                            @if ($hotel->images->first())
                                <img src="{{ \App\Support\ImageStore::url($hotel->images->first()->path) }}" alt="{{ $hotel->publicTranslation()->name }}" loading="lazy" decoding="async">
                            @endif
                            <h3>{{ $hotel->publicTranslation()->name }}</h3>
                            @if (filled($hotel->accommodation_category))
                                <p>{{ $hotel->accommodation_category }}</p>
                            @endif
                            @if (filled($hotel->publicTranslation()->summary))
                                <p>{{ $hotel->publicTranslation()->summary }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ((filled($home?->medical_heading) || filled($home?->medical_body) || $hasMedical) || $hasAttractions)
        <section class="k-section k-duo-section">
            <div class="k-wrap k-duo">
                @if (filled($home?->medical_heading) || filled($home?->medical_body) || $hasMedical)
                    <article>
                        @if (filled($home?->medical_heading))
                            <x-section-heading :title="$home->medical_heading" />
                        @else
                            <x-section-heading :title="__('ui.medical.title')" :intro="filled($home?->medical_body) ? null : __('ui.medical.home_note')" />
                        @endif
                        @foreach (\App\Support\LocaleText::paragraphs($home?->medical_body) as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                        @if ($hasMedical)
                            <p class="k-more"><a class="k-btn k-btn-line" href="{{ route('medical.index') }}">{{ __('ui.medical.visit') }}</a></p>
                        @endif
                    </article>
                @endif
                @if ($hasAttractions)
                    <article>
                        <x-section-heading :title="__('ui.attractions.title')" :intro="__('ui.attractions.description')" />
                        <a class="k-btn k-btn-line" href="{{ route('attractions.index') }}">{{ __('ui.attractions.title') }}</a>
                    </article>
                @endif
            </div>
        </section>
    @endif

    @if ($reviews->isNotEmpty())
        <section class="k-section k-quotes">
            <div class="k-wrap">
                <x-section-heading :title="__('ui.feedback.title')" :intro="__('ui.feedback.lede')" />
                <div class="k-home-quotes">
                    @foreach ($reviews as $review)
                        <blockquote class="k-quote">
                            @if (filled($review->title))
                                <h3>{{ $review->title }}</h3>
                            @endif
                            <p>{{ $review->message }}</p>
                            <footer>
                                <cite>{{ $review->publicName() }}</cite>
                                @if ($review->tour?->publicTranslation())
                                    <span>{{ $review->tour->publicTranslation()->title }}</span>
                                @endif
                            </footer>
                        </blockquote>
                    @endforeach
                </div>
                <p class="k-more"><a href="{{ route('feedback.index') }}">{{ __('ui.feedback.title') }}</a></p>
            </div>
        </section>
    @endif

    <section class="k-cta">
        <div class="k-wrap">
            <h2>{{ filled($home?->cta_heading) ? $home->cta_heading : __('ui.consultation.title') }}</h2>
            @if (filled($home?->cta_text))
                <p class="k-lede">{{ $home->cta_text }}</p>
            @else
                <p class="k-lede">{{ __('ui.consultation.lede') }}</p>
            @endif
            @include('partials.inquiry-form', ['tours' => $inquiryTours, 'destinations' => $filterOptions['destinations'], 'privacy' => $privacy, 'selectedTour' => null])
        </div>
    </section>
@endsection
