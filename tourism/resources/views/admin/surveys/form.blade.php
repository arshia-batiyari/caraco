@extends('layouts.admin')

@php
    $questions = ($record->exists ? $record->questions : collect())->values();
    if (! $locked) {
        $questions = $questions->concat([new \App\Models\SurveyQuestion(['type' => 'single', 'is_required' => true])]);
    }
@endphp

@section('content')
    <p><a href="{{ route('admin.surveys.index') }}">{{ __('admin.surveys') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->title ?? __('admin.edit')) : __('admin.create') }}</h1>
    @if ($locked)
        <p class="k-hint">{{ __('admin.survey_locked') }}</p>
    @endif
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.surveys.update', $record) : route('admin.surveys.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <label class="k-check"><input type="hidden" name="is_active" value="0"><input type="checkbox" name="is_active" value="1" @checked(filter_var(old('is_active', $record->is_active), FILTER_VALIDATE_BOOL))><span>{{ __('admin.active') }}</span></label>
        <label class="k-check"><input type="hidden" name="is_anonymous" value="0"><input type="checkbox" name="is_anonymous" value="1" @checked(filter_var(old('is_anonymous', $record->is_anonymous), FILTER_VALIDATE_BOOL)) @disabled($locked)><span>{{ __('admin.anonymous_survey') }}</span></label>
        <p class="k-hint">{{ __('admin.anonymous_survey_hint') }}</p>
        <div class="k-admin-grid">
            <div class="k-field"><label for="opens-at">{{ __('admin.opens_at') }}</label><input id="opens-at" name="opens_at" type="date" value="{{ old('opens_at', $record->opens_at?->toDateString()) }}"></div>
            <div class="k-field"><label for="closes-at">{{ __('admin.closes_at') }}</label><input id="closes-at" name="closes_at" type="date" value="{{ old('closes_at', $record->closes_at?->toDateString()) }}"></div>
        </div>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        @include('admin.partials.translation-tabs', [
            'rows' => $record->translations->keyBy('locale'),
            'fields' => ['title' => ['admin.title', false], 'description' => ['admin.description', true]],
        ])
        @unless ($locked)
            <fieldset class="k-admin-set">
                <legend>{{ __('admin.questions') }}</legend>
                @foreach ($questions as $index => $question)
                    @if ($question->exists)
                        <input type="hidden" name="questions[{{ $index }}][id]" value="{{ $question->id }}">
                    @endif
                    <div class="k-field">
                        <label>{{ __('admin.question_type') }}</label>
                        <select name="questions[{{ $index }}][type]">
                            @foreach (['single', 'multiple', 'rating', 'text'] as $type)
                                <option value="{{ $type }}" @selected(old('questions.'.$index.'.type', $question->type) === $type)>{{ __('admin.question_types.'.$type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="k-check"><input type="hidden" name="questions[{{ $index }}][is_required]" value="0"><input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" @checked(filter_var(old('questions.'.$index.'.is_required', $question->is_required), FILTER_VALIDATE_BOOL))><span>{{ __('admin.required_question') }}</span></label>
                    @include('admin.partials.translation-tabs', [
                        'namePrefix' => 'questions['.$index.'][translations]',
                        'rows' => $question->exists ? $question->translations->keyBy('locale') : collect(),
                        'fields' => ['prompt' => ['admin.prompt', false]],
                    ])
                    @php $options = ($question->exists ? $question->options : collect())->values()->concat([new \App\Models\SurveyOption, new \App\Models\SurveyOption]); @endphp
                    @foreach ($options as $optionIndex => $option)
                        @if ($option->exists)
                            <input type="hidden" name="questions[{{ $index }}][options][{{ $optionIndex }}][id]" value="{{ $option->id }}">
                        @endif
                        @include('admin.partials.translation-tabs', [
                            'namePrefix' => 'questions['.$index.'][options]['.$optionIndex.'][translations]',
                            'rows' => $option->exists ? $option->translations->keyBy('locale') : collect(),
                            'fields' => ['label' => ['admin.option', false]],
                        ])
                    @endforeach
                @endforeach
            </fieldset>
        @endunless
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <p><a href="{{ route('admin.surveys.results', $record) }}">{{ __('admin.results') }}</a></p>
        @if (auth()->user()?->isAdmin())
            <form class="k-admin-delete" method="post" action="{{ route('admin.surveys.destroy', $record) }}">
                @csrf
                @method('DELETE')
                <button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button>
            </form>
        @endif
    @endif
@endsection
