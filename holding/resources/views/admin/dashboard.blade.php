@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    <h1>{{ __('admin.dashboard') }}</h1>
    <ul class="k-admin-stats">
        <li>{{ __('admin.divisions') }}: {{ $publishedDivisions }}/{{ $divisions }}</li>
        <li>{{ __('admin.projects') }}: {{ $projects }}</li>
        <li>{{ __('admin.pages') }}: {{ $pages }}</li>
        <li>{{ __('admin.inquiries') }}: {{ $inquiries }}</li>
    </ul>
    @if ($recent->isNotEmpty())
        <h2>{{ __('admin.recent_inquiries') }}</h2>
        <ul class="k-admin-list">
            @foreach ($recent as $inquiry)
                <li><a href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }} — {{ $inquiry->email }}</a></li>
            @endforeach
        </ul>
    @endif
@endsection
