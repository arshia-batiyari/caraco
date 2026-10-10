@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section">
        <div class="k-wrap k-narrow">
            @php $copy = $survey->publicTranslation(); @endphp
            <x-section-heading :title="$copy?->title ?? __('ui.survey.title')" :intro="$copy?->description" />
            <p class="k-hint">{{ $survey->is_anonymous ? __('ui.survey.anonymous_notice') : __('ui.survey.identified_notice') }}</p>
            @if (session('sent'))
                <x-alert type="success">{{ session('sent') }}</x-alert>
            @endif
            @if ($done)
                <p>{{ __('ui.survey.already') }}</p>
            @else
                <form class="k-form" method="post" action="{{ route('feedback.surveys.store', $survey) }}">
                    @csrf
                    <p class="k-hp" aria-hidden="true">
                        <label for="company-website">Website</label>
                        <input id="company-website" name="company_website" tabindex="-1" autocomplete="off">
                    </p>
                    @if ($errors->any())
                        <x-alert type="error">{{ __('ui.validation.summary') }}</x-alert>
                    @endif
                    @unless ($survey->is_anonymous)
                        <div class="k-field">
                            <label for="survey-name">{{ __('ui.fields.name') }}</label>
                            <input id="survey-name" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="k-field {{ $errors->has('email') ? 'is-invalid' : '' }}">
                            <label for="survey-email">{{ __('ui.fields.email') }}</label>
                            <input id="survey-email" name="email" type="email" value="{{ old('email') }}">
                            @error('email')<p class="k-field-error">{{ $message }}</p>@enderror
                        </div>
                    @endunless
                    @foreach ($survey->questions as $question)
                        @php $prompt = $question->publicTranslation(); @endphp
                        @if ($prompt)
                            <fieldset class="k-choice-set {{ $errors->has('answers.'.$question->id) ? 'is-invalid' : '' }}">
                                <legend>{{ $prompt->prompt }} @if ($question->is_required)<span aria-hidden="true">*</span>@endif</legend>
                                @if ($question->type === 'single')
                                    @foreach ($question->options as $option)
                                        @if ($option->publicTranslation())
                                            <label class="k-choice">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" @checked((string) old('answers.'.$question->id) === (string) $option->id)>
                                                <span>{{ $option->publicTranslation()->label }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                @elseif ($question->type === 'multiple')
                                    @foreach ($question->options as $option)
                                        @if ($option->publicTranslation())
                                            <label class="k-choice">
                                                <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}" @checked(in_array((string) $option->id, array_map('strval', (array) old('answers.'.$question->id, [])), true))>
                                                <span>{{ $option->publicTranslation()->label }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                @elseif ($question->type === 'rating')
                                    <div class="k-choice-row">
                                        @for ($score = 1; $score <= 5; $score++)
                                            <label class="k-choice">
                                                <input type="radio" name="answers[{{ $question->id }}]" value="{{ $score }}" @checked((string) old('answers.'.$question->id) === (string) $score)>
                                                <span>{{ $score }} ★</span>
                                            </label>
                                        @endfor
                                    </div>
                                @else
                                    <textarea name="answers[{{ $question->id }}]">{{ old('answers.'.$question->id) }}</textarea>
                                @endif
                                @error('answers.'.$question->id)<p class="k-field-error">{{ $message }}</p>@enderror
                            </fieldset>
                        @endif
                    @endforeach
                    <button class="k-btn k-btn-ink" type="submit">{{ __('ui.survey.submit') }}</button>
                </form>
            @endif
        </div>
    </section>
@endsection
