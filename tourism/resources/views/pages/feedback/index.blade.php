@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading :title="__('ui.feedback.title')" :intro="__('ui.feedback.lede')" />
            @if ($surveys->contains(fn ($survey) => $survey->publicTranslation()))
                <div class="k-card-grid">
                    @foreach ($surveys as $survey)
                        @if ($survey->publicTranslation())
                            <article class="k-tour-card">
                                <h3><a href="{{ route('feedback.surveys.show', $survey) }}">{{ $survey->publicTranslation()->title }}</a></h3>
                                @if (filled($survey->publicTranslation()->description))
                                    <p>{{ $survey->publicTranslation()->description }}</p>
                                @endif
                                <p class="k-hint">{{ $survey->is_anonymous ? __('ui.survey.anonymous_notice') : __('ui.survey.identified_notice') }}</p>
                            </article>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    <section class="k-section">
        <div class="k-wrap k-feedback-layout">
            <div>
                <h2>{{ __('ui.feedback.form_title') }}</h2>
                @include('partials.feedback-form')
            </div>
            <div>
                <h2>{{ __('ui.feedback.published_title') }}</h2>
                <p class="k-hint">{{ __('ui.feedback.language_note') }}</p>
                <details class="k-filters" @if ($filtered) open @endif>
                    <summary>{{ __('ui.filters.title') }}</summary>
                    <form method="get" action="{{ route('feedback.index') }}">
                        <div class="k-field">
                            <label for="review-type">{{ __('ui.feedback.type') }}</label>
                            <select id="review-type" name="type">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @foreach (['general', 'suggestion', 'compliment'] as $type)
                                    <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ __('ui.feedback.types.'.$type) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="k-field">
                            <label for="review-rating">{{ __('ui.feedback.rating_overall') }}</label>
                            <select id="review-rating" name="rating">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @for ($score = 5; $score >= 1; $score--)
                                    <option value="{{ $score }}" @selected((string) ($filters['rating'] ?? '') === (string) $score)>{{ $score }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="k-field">
                            <label for="review-tour">{{ __('ui.fields.tour') }}</label>
                            <select id="review-tour" name="tour">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @foreach ($tours as $tour)
                                    @if ($tour->publicTranslation())
                                        <option value="{{ $tour->id }}" @selected((string) ($filters['tour'] ?? '') === (string) $tour->id)>{{ $tour->publicTranslation()->title }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="k-actions">
                            <button class="k-btn k-btn-ink" type="submit">{{ __('ui.feedback.apply') }}</button>
                            <a class="k-btn k-btn-line" href="{{ route('feedback.index') }}">{{ __('ui.filters.reset') }}</a>
                        </div>
                    </form>
                </details>
                @if ($reviews->isEmpty())
                    <x-empty-state :title="__('ui.feedback.empty_title')" :text="__('ui.feedback.empty_text')" />
                @else
                    <div class="k-review-list">
                        @foreach ($reviews as $review)
                            <article class="k-review">
                                <header>
                                    <h3>{{ $review->title }}</h3>
                                    <p>
                                        {{ $review->publicName() }}
                                        @if ($review->review?->is_verified)
                                            <span class="k-badge">{{ __('ui.feedback.verified') }}</span>
                                        @endif
                                    </p>
                                </header>
                                @if ($review->rating_overall)
                                    <p class="k-rating"><span aria-hidden="true">{{ str_repeat('★', $review->rating_overall) }}{{ str_repeat('☆', 5 - $review->rating_overall) }}</span> <span class="k-sr">{{ $review->rating_overall }}/5</span></p>
                                @endif
                                <p>{{ $review->message }}</p>
                                @php
                                    $aspects = array_filter([
                                        __('ui.feedback.rating_accommodation') => $review->rating_accommodation,
                                        __('ui.feedback.rating_transport') => $review->rating_transport,
                                        __('ui.feedback.rating_organization') => $review->rating_organization,
                                        __('ui.feedback.rating_service') => $review->rating_service,
                                    ]);
                                @endphp
                                @if ($aspects !== [])
                                    <ul class="k-chip-list">
                                        @foreach ($aspects as $label => $score)
                                            <li>{{ $label }} {{ $score }}/5</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <p class="k-hint">
                                    <time datetime="{{ $review->created_at?->toDateString() }}">{{ $review->created_at?->toDateString() }}</time>
                                    · {{ __('ui.feedback.types.'.$review->type) }}
                                    · {{ __('ui.feedback.original_language') }} {{ config('karako.locales.'.$review->locale.'.native', strtoupper((string) $review->locale)) }}
                                    @if ($review->tour?->publicTranslation())
                                        · <a href="{{ $review->tour->publicUrl('tours.show') }}">{{ $review->tour->publicTranslation()->title }}</a>
                                    @endif
                                    @if ($review->service?->publicTranslation())
                                        · {{ $review->service->publicTranslation()->name }}
                                    @endif
                                </p>
                            </article>
                        @endforeach
                    </div>
                    {{ $reviews->links() }}
                @endif
            </div>
        </div>
    </section>
@endsection
