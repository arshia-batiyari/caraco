@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    <h1>{{ __('admin.dashboard') }}</h1>
    <ul class="k-admin-stats">
        <li>{{ __('admin.tours') }}: {{ $publishedTours }}/{{ $tours }}</li>
        <li>{{ __('admin.hotels') }}: {{ $hotels }}</li>
        <li>{{ __('admin.inquiries') }}: {{ $openInquiries }}/{{ $inquiries }}</li>
        <li>{{ __('admin.pending_feedback') }}: {{ $pendingReviews }}</li>
    </ul>
    @if ($recent->isNotEmpty())
        <h2>{{ __('admin.recent_inquiries') }}</h2>
        <ul class="k-admin-list">
            @foreach ($recent as $inquiry)
                <li><a href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }} — {{ $inquiry->email }} — {{ __('admin.statuses.'.$inquiry->status) }}</a></li>
            @endforeach
        </ul>
    @endif
@endsection
