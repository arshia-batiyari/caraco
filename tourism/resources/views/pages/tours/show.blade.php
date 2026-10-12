@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section k-tour">
        <div class="k-wrap">
            <header class="k-section-head">
                <p class="k-eyebrow">{{ $tour->scope === 'domestic' ? __('ui.tours.domestic') : __('ui.tours.international') }}</p>
                <h1>{{ $translation->title }}</h1>
                @if ($tour->destination?->publicTranslation())
                    <p><a href="{{ $tour->destination->publicUrl('destinations.show') }}">{{ $tour->destination->publicTranslation()->name }}</a></p>
                @endif
                @php
                    $price = \App\Support\TourPrice::format(\App\Support\TourPrice::effectiveAmount($tour), $tour->currency);
                    $base = \App\Support\TourPrice::format($tour->base_price, $tour->currency);
                    $discounted = \App\Support\TourPrice::active($tour);
                @endphp
                @if ($price)
                    <p class="k-price k-tour-price">
                        @if ($discounted)
                            <x-badge>
                                @if (\App\Support\TourPrice::showsPercent($tour))
                                    {{ __('ui.tours.percent_off', ['n' => $tour->discount_percent]) }}
                                @else
                                    {{ __('ui.tours.discounted') }}
                                @endif
                            </x-badge>
                            <s>{{ $base }}</s>
                        @endif
                        <strong>{{ $price }}</strong>
                    </p>
                @endif
            </header>

            @if ($tour->images->isNotEmpty())
                <div class="k-gallery-grid">
                    @foreach ($tour->images as $image)
                        <img src="{{ \App\Support\ImageStore::url($image->path) }}" alt="{{ $translation->title }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                    @endforeach
                </div>
            @elseif ($cover = \App\Support\PreviewMedia::tour($tour))
                <img class="k-detail-image" src="{{ $cover }}" alt="{{ $translation->title }}" fetchpriority="high" decoding="async">
            @endif

            @if (filled($translation->summary) || filled($translation->description))
                <div class="k-narrow">
                    @foreach (\App\Support\LocaleText::paragraphs($translation->summary) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                    @foreach (\App\Support\LocaleText::paragraphs($translation->description) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>
            @endif

            <dl class="k-facts">
                @if ($tour->duration_days)
                    <div>
                        <dt>{{ __('ui.tours.days_label') }}</dt>
                        <dd>{{ $tour->duration_days }}</dd>
                    </div>
                @endif
                @if ($tour->duration_nights !== null)
                    <div>
                        <dt>{{ __('ui.tours.nights_label') }}</dt>
                        <dd>{{ $tour->duration_nights }}</dd>
                    </div>
                @endif
                @if (filled($translation->departure_location))
                    <div>
                        <dt>{{ __('ui.tours.departure_point') }}</dt>
                        <dd>{{ $translation->departure_location }}</dd>
                    </div>
                @endif
                @if ($tour->category?->publicTranslation())
                    <div>
                        <dt>{{ __('ui.filters.category') }}</dt>
                        <dd><a href="{{ $tour->category->publicUrl('categories.show') }}">{{ $tour->category->publicTranslation()->name }}</a></dd>
                    </div>
                @endif
                @if ($tour->travelStyle?->publicTranslation())
                    <div>
                        <dt>{{ __('ui.tours.style') }}</dt>
                        <dd>{{ $tour->travelStyle->publicTranslation()->name }}</dd>
                    </div>
                @endif
            </dl>

            @php $departures = $tour->departures->where('is_published', true); @endphp
            @if ($departures->isNotEmpty())
                <h2>{{ __('ui.tours.dates') }}</h2>
                <ul>
                    @foreach ($departures as $departure)
                        <li>{{ $departure->departure_date->toDateString() }} – {{ $departure->return_date->toDateString() }}</li>
                    @endforeach
                </ul>
            @endif

            @if (filled($translation->meeting_instructions))
                <h2>{{ __('ui.tours.meeting') }}</h2>
                @foreach (\App\Support\LocaleText::paragraphs($translation->meeting_instructions) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            @endif

            @if ($tour->transports->isNotEmpty() || filled($translation->transport_details))
                <h2>{{ __('ui.tours.transport') }}</h2>
                @if ($tour->transports->isNotEmpty())
                    <ul>
                        @foreach ($tour->transports as $transport)
                            @if ($transport->publicTranslation())
                                <li>{{ $transport->publicTranslation()->name }}</li>
                            @endif
                        @endforeach
                    </ul>
                @endif
                @foreach (\App\Support\LocaleText::paragraphs($translation->transport_details) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            @endif

            @if ($tour->stays->isNotEmpty() || filled($translation->meals))
                <h2>{{ __('ui.tours.accommodation') }}</h2>
                @foreach ($tour->stays as $stay)
                    @if ($stay->hotel?->publicTranslation())
                        <section class="k-stay">
                            <h3>{{ $stay->hotel->publicTranslation()->name }}</h3>
                            @if (filled($stay->hotel->accommodation_category))
                                <p>{{ $stay->hotel->accommodation_category }}</p>
                            @endif
                            @if (filled($stay->hotel->publicTranslation()->address))
                                <p>{{ $stay->hotel->publicTranslation()->address }}</p>
                            @endif
                            @foreach (\App\Support\LocaleText::paragraphs($stay->hotel->publicTranslation()->description) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                            @if (filled($stay->hotel->publicTranslation()->room_details))
                                <p>{{ $stay->hotel->publicTranslation()->room_details }}</p>
                            @endif
                            @php $facilities = $stay->hotel->facilities->filter(fn ($facility) => filled($facility->publicTranslation()?->name)); @endphp
                            @if ($facilities->isNotEmpty())
                                <ul>
                                    @foreach ($facilities as $facility)
                                        <li>{{ $facility->publicTranslation()->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                            @php $stayImage = \App\Support\PreviewMedia::hotel($stay->hotel); @endphp
                            @if ($stay->hotel->images->isNotEmpty())
                                <div class="k-gallery-grid">
                                    @foreach ($stay->hotel->images as $image)
                                        <img src="{{ \App\Support\ImageStore::url($image->path) }}" alt="{{ $stay->hotel->publicTranslation()->name }}" loading="lazy" decoding="async">
                                    @endforeach
                                </div>
                            @elseif ($stayImage)
                                <img class="k-detail-image" src="{{ $stayImage }}" alt="{{ $stay->hotel->publicTranslation()->name }}" loading="lazy" decoding="async">
                            @endif
                            @if (filled($stay->publicTranslation()?->arrangement))
                                <p>{{ $stay->publicTranslation()->arrangement }}</p>
                            @endif
                        </section>
                    @endif
                @endforeach
                @foreach (\App\Support\LocaleText::paragraphs($translation->meals) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            @endif

            @php
                $included = $tour->services->where('type', 'included');
                $excluded = $tour->services->where('type', 'excluded');
            @endphp
            @if ($included->isNotEmpty() || $excluded->isNotEmpty())
                <div class="k-inclusions">
                    @if ($included->isNotEmpty())
                        <section>
                            <h2>{{ __('ui.tours.included') }}</h2>
                            <ul>
                                @foreach ($included as $service)
                                    @if ($service->publicTranslation())
                                        <li>{{ $service->publicTranslation()->name }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </section>
                    @endif
                    @if ($excluded->isNotEmpty())
                        <section>
                            <h2>{{ __('ui.tours.excluded') }}</h2>
                            <ul>
                                @foreach ($excluded as $service)
                                    @if ($service->publicTranslation())
                                        <li>{{ $service->publicTranslation()->name }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>
            @endif

            @if ($tour->days->isNotEmpty())
                <h2>{{ __('ui.tours.itinerary') }}</h2>
                <ol class="k-timeline">
                    @foreach ($tour->days as $day)
                        @php $dayText = $day->translated(); @endphp
                        <li>
                            <p class="k-timeline-day">{{ __('ui.tours.day', ['n' => $day->day_number]) }}</p>
                            @if (filled($dayText?->title))
                                <h3>{{ $dayText->title }}</h3>
                            @endif
                            @foreach (\App\Support\LocaleText::paragraphs($dayText?->body) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </li>
                    @endforeach
                </ol>
            @endif

            @if ($tour->routePoints->isNotEmpty())
                <h2>{{ __('ui.tours.route') }}</h2>
                <figure class="k-route" @if ($tiles && $mapPoints) data-route-map data-tile-base="{{ $tiles }}" data-points='@json($mapPoints)' @endif>
                    @if ($map)
                        {!! $map !!}
                    @endif
                    <ol>
                        @foreach ($tour->routePoints as $point)
                            @php $pointText = $point->translated(); @endphp
                            <li>
                                {{ filled($pointText?->name) ? $pointText->name : __('ui.tours.point', ['n' => $loop->iteration]) }}
                                @if (filled($pointText?->description))
                                    — {{ $pointText->description }}
                                @endif
                            </li>
                        @endforeach
                    </ol>
                    @if ($tiles)
                        <figcaption class="k-hint">{{ __('ui.tours.map_credit') }}</figcaption>
                    @endif
                </figure>
            @endif

            @foreach (['notes' => 'ui.tours.notes', 'requirements' => 'ui.tours.requirements', 'restrictions' => 'ui.tours.restrictions', 'terms' => 'ui.tours.terms', 'cancellation' => 'ui.tours.cancellation'] as $field => $label)
                @if (filled($translation->{$field}))
                    <h2>{{ __($label) }}</h2>
                    @foreach (\App\Support\LocaleText::paragraphs($translation->{$field}) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endif
            @endforeach

            <p class="k-more"><a class="k-btn k-btn-gold" href="{{ route('consultation', ['tour' => $tour->id]) }}">{{ __('ui.consultation.about_tour') }}</a></p>
            <p class="k-hint">{{ __('ui.consultation.not_reservation') }}</p>

            @if ($faqs->isNotEmpty())
                <h2>{{ __('ui.tours.faq') }}</h2>
                @foreach ($faqs as $faq)
                    @php $faqText = $faq->publicTranslation(); @endphp
                    @if ($faqText)
                        <details>
                            <summary>{{ $faqText->question }}</summary>
                            @foreach (\App\Support\LocaleText::paragraphs($faqText->answer) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </details>
                    @endif
                @endforeach
            @endif

            @if ($related->isNotEmpty())
                <h2>{{ __('ui.tours.related') }}</h2>
                <div class="k-card-grid">
                    @foreach ($related as $item)
                        @include('partials.tour-card', ['tour' => $item])
                    @endforeach
                </div>
            @endif
        </div>
    </article>
@endsection
