@extends('layouts.admin')

@section('title', __('admin.medical_inquiries'))

@section('content')
    <h1>{{ __('admin.medical_inquiries') }}</h1>
    @if ($records->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.fields.name') }}</th>
                        <th>{{ __('ui.medical.service') }}</th>
                        <th>{{ __('admin.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($records as $record)
                        <tr>
                            <td><a href="{{ route('admin.medical.inquiries.show', $record) }}">{{ $record->name }}</a></td>
                            <td>{{ $record->service?->translations->first()?->name }}</td>
                            <td>{{ __('admin.statuses.'.$record->status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $records->links() }}
    @endif
@endsection
