@php
    $translation = $tour->publicTranslation();
    $url = $tour->publicUrl('tours.show');
    $destination = $tour->destination?->publicTranslation();
    $price = \App\Support\TourPrice::format(\App\Support\TourPrice::effectiveAmount($tour), $tour->currency);
    $base = \App\Support\TourPrice::format($tour->base_price, $tour->currency);
    $discounted = \App\Support\TourPrice::active($tour);
    $departure = $tour->nextDeparture();
@endphp
@if ($translation && $url)
    <article class="k-tour-card">
        <a href="{{ $url }}">
            @php $cover = \App\Support\PreviewMedia::tour($tour); @endphp
            @if ($cover)
                <img src="{{ $cover }}" alt="{{ $translation->title }}" loading="lazy" decoding="async">
            @endif
            <div>
                @if ($discounted)
                    <x-badge>
                        @if (\App\Support\TourPrice::showsPercent($tour))
                            {{ __('ui.tours.percent_off', ['n' => $tour->discount_percent]) }}
                        @else
                            {{ __('ui.tours.discounted') }}
                        @endif
                    </x-badge>
                @endif
                <h3>{{ $translation->title }}</h3>
                @if ($destination)
                    <p>{{ $destination->name }}</p>
                @endif
                @if ($tour->duration_days)
                    <p>
                        {{ __('ui.tours.days', ['n' => $tour->duration_days]) }}
                        @if ($tour->duration_nights !== null)
                            · {{ __('ui.tours.nights', ['n' => $tour->duration_nights]) }}
                        @endif
                    </p>
                @endif
                @if ($departure)
                    <p>{{ $departure->departure_date->toDateString() }} – {{ $departure->return_date->toDateString() }}</p>
                @endif
                @if ($price)
                    <p class="k-price">
                        @if ($discounted && $base)
                            <s>{{ $base }}</s>
                        @endif
                        <strong>{{ $price }}</strong>
                    </p>
                @endif
            </div>
        </a>
    </article>
@endif
