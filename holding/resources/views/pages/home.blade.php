@extends('layouts.site')

@section('content')
    @if (filled($home?->hero_title))
        <x-hero
            class="k-stage"
            variant="editorial"
            :eyebrow="$home->hero_eyebrow"
            :title="$home->hero_title"
            :lede="$home->hero_lede"
            :image-url="$heroImage"
            :image-alt="$home->hero_title"
            :lang="$home->locale"
        >
            <x-slot:actions>
                @if ($divisions->isNotEmpty())
                    <a class="k-btn k-btn-ink" href="{{ route('divisions.index') }}">{{ __('ui.nav.divisions') }}</a>
                @endif
                <a class="k-btn k-btn-line" href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
            </x-slot:actions>
        </x-hero>
    @else
        <section class="k-masthead k-stage k-stage-mark">
            <div class="k-wrap k-masthead-grid">
                <div>
                    @if (filled($home?->hero_eyebrow))
                        <p class="k-eyebrow">{{ $home->hero_eyebrow }}</p>
                    @endif
                    <h1 class="k-wordmark"><x-logo size="masthead" /></h1>
                    @if (filled($home?->hero_lede))
                        <p class="k-lede">{{ $home->hero_lede }}</p>
                    @endif
                    <div class="k-actions">
                        @if ($divisions->isNotEmpty())
                            <a class="k-btn k-btn-gold" href="{{ route('divisions.index') }}">{{ __('ui.nav.divisions') }}</a>
                        @endif
                        <a class="k-btn k-btn-line" href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
                    </div>
                </div>
                @if ($heroImage)
                    <img src="{{ $heroImage }}" alt="{{ filled($home?->hero_lede) ? $home->hero_lede : 'KARAKO' }}" fetchpriority="high" decoding="async">
                @endif
            </div>
        </section>
    @endif

    <x-video-player :video="$video" />

    @if (filled($home?->intro_heading) || filled($home?->intro_body) || $introImage)
        <section class="k-section k-challenge">
            <div class="k-challenge-grid">
                <p class="k-challenge-no">01</p>
                <div>
                    @if (filled($home?->intro_heading))
                        <x-section-heading :title="$home->intro_heading" />
                    @endif
                    <x-prose-body :body="$home->intro_body ?? null" />
                </div>
            </div>
            @if ($introImage)
                <figure class="k-challenge-media">
                    <img src="{{ $introImage }}" alt="{{ filled($home?->intro_heading) ? $home->intro_heading : '' }}" loading="lazy" decoding="async">
                </figure>
            @endif
        </section>
    @endif

    @if ($divisions->isNotEmpty())
        <x-division-list :divisions="$divisions" :heading="filled($home?->divisions_heading) ? $home->divisions_heading : __('ui.nav.divisions')" />
    @endif

    @if ($projects->isNotEmpty())
        <section class="k-section k-projects k-reel">
            <div class="k-wrap">
                <x-section-heading :title="filled($home?->projects_heading) ? $home->projects_heading : __('ui.nav.projects')" />
                <div class="k-portfolio">
                    @foreach ($projects as $project)
                        @php
                            $translation = $project->publicTranslation();
                            $image = \App\Support\ImageStore::url($project->image_path);
                        @endphp
                        <a class="k-portfolio-item {{ $loop->first ? 'is-lead' : '' }}" href="{{ $project->publicUrl('projects.show') }}">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $translation->title }}" loading="lazy" decoding="async">
                            @endif
                            <span class="k-index-title">{{ $translation->title }}</span>
                            @if (filled($translation->summary))
                                <span class="k-index-text">{{ $translation->summary }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
                <p class="k-more"><a href="{{ route('projects.index') }}">{{ __('ui.nav.projects') }}</a></p>
            </div>
        </section>
    @endif

    @if (filled($home?->approach_heading) || filled($home?->approach_body))
        <section class="k-section k-approach">
            <div class="k-wrap k-narrow">
                @if (filled($home?->approach_heading))
                    <x-section-heading :title="$home->approach_heading" />
                @endif
                <x-prose-body :body="$home->approach_body ?? null" />
            </div>
        </section>
    @endif

    <x-cta
        :title="filled($home?->cta_heading) ? $home->cta_heading : __('ui.contact_page.cta_title')"
        :text="filled($home?->cta_text) ? $home->cta_text : null"
        :href="route('contact')"
        :label="__('ui.contact_page.cta_label')"
    />

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
