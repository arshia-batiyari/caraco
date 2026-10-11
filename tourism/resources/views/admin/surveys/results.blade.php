@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.surveys.edit', $record) }}">{{ $record->translations->first()?->title ?? __('admin.surveys') }}</a></p>
    <h1>{{ __('admin.results') }}</h1>
    <p>{{ __('admin.responses') }}: {{ $record->responses()->count() }}</p>
    <p>{{ $record->is_anonymous ? __('ui.survey.anonymous_notice') : __('ui.survey.identified_notice') }}</p>
    <p><a class="k-btn k-btn-line" href="{{ route('admin.surveys.export', $record) }}">{{ __('admin.export') }}</a></p>
    @foreach ($summary as $block)
        <section class="k-admin-set">
            <h2>{{ $block['question']->publicTranslation()?->prompt }}</h2>
            @if ($block['average'] !== null)
                <p>{{ __('admin.average') }}: {{ $block['average'] }}</p>
            @endif
            @if ($block['rows'] !== [])
                <ul>
                    @foreach ($block['rows'] as $row)
                        <li>{{ $row['label'] }}: {{ $row['count'] }}</li>
                    @endforeach
                </ul>
            @endif
            @foreach ($block['texts'] as $text)
                <blockquote>{{ $text }}</blockquote>
            @endforeach
        </section>
    @endforeach
@endsection
