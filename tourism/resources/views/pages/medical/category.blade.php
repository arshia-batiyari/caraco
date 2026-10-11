@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <article class="k-section">
        <div class="k-wrap">
            <header class="k-section-head">
                <h1>{{ $translation->name }}</h1>
                @if (filled($translation->summary))<p class="k-lede">{{ $translation->summary }}</p>@endif
            </header>
            <p class="k-notice">{{ __('ui.medical.disclaimer') }}</p>
            @foreach (\App\Support\LocaleText::paragraphs($translation->description) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
            @if ($services->isEmpty())
                <x-empty-state :title="__('ui.medical.empty_title')" :text="__('ui.medical.empty_text')" />
            @else
                <div class="k-card-grid">
                    @foreach ($services as $service)
                        @include('partials.medical-card', ['record' => $service, 'route' => 'medical.services.show'])
                    @endforeach
                </div>
            @endif
        </div>
    </article>
@endsection
