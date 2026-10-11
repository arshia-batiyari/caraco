@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <header class="k-medical-hero">
        <div class="k-wrap k-narrow">
            <p class="k-logo-plate"><x-logo /></p>
            <h1>{{ filled($copy?->title) ? $copy->title : __('ui.medical.title') }}</h1>
            @if (filled($copy?->summary))
                <p class="k-lede">{{ $copy->summary }}</p>
            @endif
            <p class="k-notice">{{ __('ui.medical.disclaimer') }}</p>
        </div>
    </header>

    @if (filled($copy?->body))
        <section class="k-section">
            <div class="k-wrap k-narrow">
                @foreach (\App\Support\LocaleText::paragraphs($copy->body) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </section>
    @endif

    @if ($categories->isNotEmpty())
        <section class="k-section">
            <div class="k-wrap">
                <x-section-heading :title="__('ui.medical.services')" />
                <div class="k-card-grid">
                    @foreach ($categories as $category)
                        @php $label = $category->publicTranslation(); $url = $category->publicUrl('medical.categories.show'); @endphp
                        @if ($label && $url)
                            <article class="k-editorial-card">
                                <h2><a href="{{ $url }}">{{ $label->name }}</a></h2>
                                @if (filled($label->summary))<p>{{ $label->summary }}</p>@endif
                            </article>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="k-section">
        <div class="k-wrap">
            <div class="k-section-head">
                <h2>{{ __('ui.medical.services') }}</h2>
                <a href="{{ route('medical.services') }}">{{ __('ui.medical.services') }}</a>
            </div>
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
    </section>

    @if ($steps->isNotEmpty())
        <section class="k-section k-medical-band">
            <div class="k-wrap k-narrow">
                <x-section-heading :title="__('ui.medical.journey')" :intro="__('ui.medical.journey_note')" />
                <ol class="k-steps">
                    @foreach ($steps as $step)
                        <li>
                            <h3>{{ $step->title }}</h3>
                            @foreach (\App\Support\LocaleText::paragraphs($step->body) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if ($providers->isNotEmpty())
        <section class="k-section">
            <div class="k-wrap">
                <x-section-heading :title="__('ui.medical.clinics')" />
                <div class="k-card-grid">
                    @foreach ($providers as $provider)
                        @include('partials.medical-card', ['record' => $provider, 'route' => 'medical.providers.show'])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (filled($copy?->travel_assistance) || filled($copy?->stay_assistance))
        <section class="k-section">
            <div class="k-wrap k-narrow">
                @if (filled($copy?->travel_assistance))
                    <h2>{{ __('ui.medical.travel') }}</h2>
                    @foreach (\App\Support\LocaleText::paragraphs($copy->travel_assistance) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endif
                @if (filled($copy?->stay_assistance))
                    <h2>{{ __('ui.medical.stay') }}</h2>
                    @foreach (\App\Support\LocaleText::paragraphs($copy->stay_assistance) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endif
            </div>
        </section>
    @endif

    @if ($faqs->isNotEmpty())
        <section class="k-section">
            <div class="k-wrap k-narrow">
                <x-section-heading :title="__('ui.medical.faq')" />
                <div class="k-faq">
                    @foreach ($faqs as $faq)
                        <details>
                            <summary>{{ $faq->question }}</summary>
                            @foreach (\App\Support\LocaleText::paragraphs($faq->answer) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </details>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="k-section" id="consultation">
        <div class="k-wrap k-narrow">
            <x-section-heading :title="__('ui.medical.submit')" />
            @if (filled($copy?->contact_note))
                <h2>{{ __('ui.medical.contact') }}</h2>
                @foreach (\App\Support\LocaleText::paragraphs($copy->contact_note) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            @endif
            <p class="k-notice">{{ __('ui.medical.disclaimer') }}</p>
            @include('partials.medical-inquiry-form', ['services' => $inquiryServices, 'privacy' => $privacy, 'selected' => request('service')])
        </div>
    </section>
@endsection
