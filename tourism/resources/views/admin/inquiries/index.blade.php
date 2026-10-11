@extends('layouts.admin')

@section('title', __('admin.inquiries'))

@section('content')
    <h1>{{ __('admin.inquiries') }}</h1>
    @include('admin.partials.filters', [
        'showPublished' => false,
        'showStatus' => true,
        'statuses' => ['new', 'reviewing', 'assigned', 'closed'],
    ])
    @if ($records->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead><tr><th>{{ __('ui.fields.name') }}</th><th>{{ __('admin.status') }}</th></tr></thead>
                <tbody>
                    @foreach ($records as $inquiry)
                        <tr>
                            <td><a href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }}</a></td>
                            <td>{{ __('admin.statuses.'.$inquiry->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    @endif
@endsection
