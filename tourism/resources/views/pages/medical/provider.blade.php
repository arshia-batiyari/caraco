@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section">
        <div class="k-wrap">
            <header class="k-narrow">
                <p class="k-eyebrow">{{ __('ui.medical.clinics') }}</p>
                <h1>{{ $translation->name }}</h1>
                @if ($record->destination?->is_published && $record->destination->publicTranslation())
                    <p><a href="{{ $record->destination->publicUrl('destinations.show') }}">{{ $record->destination->publicTranslation()->name }}</a></p>
                @endif
                @if (filled($translation->summary))<p class="k-lede">{{ $translation->summary }}</p>@endif
                <p class="k-notice">{{ __('ui.medical.disclaimer') }}</p>
                <p>{{ __('ui.medical.no_credentials') }}</p>
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
            @php $services = $record->services->filter(fn ($service) => $service->is_published && $service->publicUrl('medical.services.show')); @endphp
            @if ($services->isNotEmpty())
                <h2>{{ __('ui.medical.services') }}</h2>
                <ul class="k-inline-links">
                    @foreach ($services as $service)
                        <li><a href="{{ $service->publicUrl('medical.services.show') }}">{{ $service->publicTranslation()->name }}</a></li>
                    @endforeach
                </ul>
            @endif
            @php $doctors = $record->doctors->filter(fn ($doctor) => $doctor->is_published && $doctor->publicUrl('medical.doctors.show')); @endphp
            @if ($doctors->isNotEmpty())
                <h2>{{ __('ui.medical.doctors') }}</h2>
                <ul class="k-inline-links">
                    @foreach ($doctors as $doctor)
                        <li><a href="{{ $doctor->publicUrl('medical.doctors.show') }}">{{ $doctor->publicTranslation()->name }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>
    </article>
@endsection
