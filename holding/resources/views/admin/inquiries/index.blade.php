@extends('layouts.admin')

@section('title', __('admin.inquiries'))

@section('content')
    <h1>{{ __('admin.inquiries') }}</h1>
    @if ($inquiries->isEmpty())
        <p>{{ __('admin.empty') }}</p>
    @else
        <div class="k-table-wrap">
            <table class="k-table">
                <thead>
                    <tr>
                        <th>{{ __('ui.fields.name') }}</th>
                        <th>{{ __('ui.fields.email') }}</th>
                        <th>{{ __('admin.locale') }}</th>
                        <th>{{ __('admin.received') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($inquiries as $inquiry)
                        <tr>
                            <td><a href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }}</a></td>
                            <td>{{ $inquiry->email }}</td>
                            <td>{{ $inquiry->locale }}</td>
                            <td>{{ $inquiry->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $inquiries->links() }}
    @endif
@endsection
