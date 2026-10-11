@extends('layouts.admin')

@section('content')
    <div class="k-admin-heading">
        <h1>{{ __('admin.surveys') }}</h1>
        <a class="k-btn k-btn-ink" href="{{ route('admin.surveys.create') }}">{{ __('admin.create') }}</a>
    </div>
    @include('admin.partials.filters', ['showPublished' => false])
    @if ($records->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead><tr><th>{{ __('admin.title') }}</th><th>{{ __('admin.responses') }}</th></tr></thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td><a href="{{ route('admin.surveys.edit', $record) }}">{{ $record->translations->first()?->title ?? '#'.$record->id }}</a></td>
                            <td><a href="{{ route('admin.surveys.results', $record) }}">{{ $record->responses_count }}</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    @endif
@endsection
