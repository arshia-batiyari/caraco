@extends('layouts.site')

@section('content')
    @php
        $heroTitle = filled($home?->hero_title) ? trim((string) $home->hero_title) : '';
        $heroWords = $heroTitle !== '' ? (preg_split('/\s+/u', $heroTitle) ?: []) : [];
        $heroAccent = $heroWords !== [] ? array_pop($heroWords) : null;
        $heroLead = implode(' ', $heroWords);
        $heroAccentLatin = $heroAccent !== null && preg_match('/\p{Arabic}/u', $heroAccent) !== 1;
        $hasVideo = is_array($video) && filled($video['src'] ?? null);
    @endphp

    @if ($hasVideo)
        <x-video-player :video="$video" />
    @else
        <section class="bx-reel" aria-label="{{ __('ui.video.title') }}">
            <div class="bx-shell">
                <div class="bx-reel-stage {{ $heroImage ? 'has-photo' : '' }}">
                    @if ($heroImage)
                        <img src="{{ $heroImage }}" alt="" decoding="async">
                    @endif
                    <div class="bx-reel-copy">
                        <span class="bx-play" aria-hidden="true"></span>
                        <p>{{ __('ui.video.title') }}</p>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="bx-hero">
        <div class="bx-shell bx-hero-panel">
            <div class="bx-hero-copy bx-rise">
                @if (filled($home?->hero_eyebrow))
                    <p class="bx-kicker">{{ $home->hero_eyebrow }}</p>
                @endif
                @if ($heroTitle !== '')
                    <h1>
                        @if ($heroLead !== '')
                            {{ $heroLead }}
                        @endif
                        <em @class(['is-latin' => $heroAccentLatin])>{{ $heroAccent }}</em>
                    </h1>
                @else
                    <h1 class="bx-logo-title"><x-logo size="masthead" /></h1>
                @endif
                @if (filled($home?->hero_lede))
                    <p class="bx-lede">{{ $home->hero_lede }}</p>
                @endif
                <div class="bx-hero-actions">
                    @if ($divisions->isNotEmpty())
                        <a class="bx-mark" href="{{ route('divisions.index') }}" aria-label="{{ __('ui.nav.divisions') }}">
                            <svg viewBox="0 0 64 64" width="72" height="72" aria-hidden="true">
                                <rect x="2.5" y="2.5" width="59" height="59" rx="18" fill="none" stroke="currentColor" stroke-width="1.4"></rect>
                                <path d="M26 40 40 26M30 26h10v10" fill="none" stroke="currentColor" stroke-width="1.4"></path>
                            </svg>
                        </a>
                        <a class="bx-textlink" href="{{ route('divisions.index') }}">
                            {{ __('ui.nav.divisions') }}
                            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.6"></path></svg>
                        </a>
                    @endif
                    <a class="bx-pill" href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
                </div>
            </div>
            @if ($heroImage)
                <figure class="bx-hero-photo bx-rise">
                    <img src="{{ $heroImage }}" alt="{{ $heroTitle !== '' ? $heroTitle : 'KARAKO' }}" fetchpriority="high" decoding="async">
                </figure>
            @endif
        </div>
    </section>

    @if (filled($home?->intro_heading) || filled($home?->intro_body) || $introImage)
        <section class="bx-about">
            <div class="bx-shell bx-about-grid">
                @if ($introImage)
                    <figure class="bx-about-photo bx-rise">
                        <img src="{{ $introImage }}" alt="{{ filled($home?->intro_heading) ? $home->intro_heading : '' }}" loading="lazy" decoding="async">
                    </figure>
                @endif
                <div class="bx-rise">
                    @if (filled($home?->intro_heading))
                        <h2>{{ $home->intro_heading }}</h2>
                    @endif
                    <x-prose-body :body="$home->intro_body ?? null" />
                </div>
            </div>
        </section>
    @endif

    @if ($divisions->isNotEmpty())
        <div class="bx-marquee" aria-hidden="true">
            <div class="bx-marquee-track">
                @foreach ([1, 2] as $copy)
                    @foreach ($divisions as $division)
                        <span>{{ $division->publicTranslation()->title }}</span>
                    @endforeach
                @endforeach
            </div>
        </div>
        <x-division-list :divisions="$divisions" :heading="filled($home?->divisions_heading) ? $home->divisions_heading : __('ui.nav.divisions')" />
    @endif

    @if ($projects->isNotEmpty())
        <section class="bx-band">
            <div class="bx-shell">
                <div class="bx-band-head bx-rise">
                    <h2>{{ filled($home?->projects_heading) ? $home->projects_heading : __('ui.nav.projects') }}</h2>
                </div>
                <div class="bx-cards">
                    @foreach ($projects as $project)
                        @php
                            $translation = $project->publicTranslation();
                            $image = \App\Support\ImageStore::url($project->image_path);
                        @endphp
                        <a class="bx-card bx-rise" href="{{ $project->publicUrl('projects.show') }}" style="--bx-d: {{ min($loop->index, 6) * 70 }}ms">
                            @if ($image)
                                <img src="{{ $image }}" alt="{{ $translation->title }}" loading="lazy" decoding="async">
                            @endif
                            <span class="bx-card-title">{{ $translation->title }}</span>
                            @if (filled($translation->summary))
                                <span class="bx-card-text">{{ $translation->summary }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (filled($home?->approach_heading) || filled($home?->approach_body))
        <section class="bx-about">
            <div class="bx-shell bx-rise">
                @if (filled($home?->approach_heading))
                    <h2>{{ $home->approach_heading }}</h2>
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
