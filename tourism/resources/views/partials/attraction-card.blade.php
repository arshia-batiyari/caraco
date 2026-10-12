@php
    $label = $record->publicTranslation();
    $url = $record->publicUrl('attractions.show');
    $image = \App\Support\PreviewMedia::attraction($record);
    $place = $record->publicPlace();
    $city = $place['city'];
    $country = $place['country'];
@endphp
@if ($label && $url)
    <article class="k-editorial-card k-attraction-card">
        <a href="{{ $url }}">
            @if ($image)
                <img src="{{ $image }}" alt="{{ filled($label->cover_alt) ? $label->cover_alt : $label->title }}" loading="lazy" decoding="async">
            @endif
            @if ($record->showsUnesco())
                <x-badge>{{ __('ui.attractions.unesco') }}</x-badge>
            @endif
            <h3>{{ $label->title }}</h3>
            @if ($city || $country)
                <p>{{ collect([$city, $country])->filter()->implode(' · ') }}</p>
            @endif
            @if (filled($label->summary))<p>{{ $label->summary }}</p>@endif
        </a>
    </article>
@endif
