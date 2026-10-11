@extends('layouts.admin')

@section('content')
    <h1>{{ __('admin.feedback') }}</h1>
    <form class="k-admin-filters" method="get">
        <div class="k-field">
            <label for="admin-q">{{ __('admin.search') }}</label>
            <input id="admin-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" maxlength="80">
        </div>
        <select name="type">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach (['general', 'complaint', 'suggestion', 'compliment'] as $type)
                <option value="{{ $type }}" @selected(($filters['type'] ?? '') === $type)>{{ __('ui.feedback.types.'.$type) }}</option>
            @endforeach
        </select>
        <select name="status">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach (['pending', 'approved', 'rejected', 'hidden'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __('admin.review_statuses.'.$status) }}</option>
            @endforeach
        </select>
        <select name="complaint">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach (['new', 'reviewing', 'awaiting', 'resolved', 'closed'] as $status)
                <option value="{{ $status }}" @selected(($filters['complaint'] ?? '') === $status)>{{ __('admin.complaint_statuses.'.$status) }}</option>
            @endforeach
        </select>
        <button class="k-btn k-btn-ink" type="submit">{{ __('ui.feedback.apply') }}</button>
    </form>
    @if ($records->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>{{ __('admin.title') }}</th>
                        <th>{{ __('admin.type') }}</th>
                        <th>{{ __('admin.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td><a href="{{ route('admin.feedback.show', $record) }}">{{ $record->title }}</a></td>
                            <td>{{ __('ui.feedback.types.'.$record->type) }}</td>
                            <td>{{ $record->review ? __('admin.review_statuses.'.$record->review->status) : __('admin.complaint_statuses.'.($record->followUps->last()?->complaint_status ?? 'new')) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    @endif
@endsection
