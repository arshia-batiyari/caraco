@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    <h1>{{ __('admin.dashboard') }}</h1>
    <p class="k-admin-lead">{{ __('admin.dashboard_lead') }}</p>
    <ul class="k-admin-stats">
        <li>
            <a href="{{ route('admin.tours.index', ['published' => 1]) }}">
                <span>{{ __('admin.tours') }}</span>
                <strong>{{ $publishedTours }}/{{ $tours }}</strong>
                <small>{{ __('admin.drafts') }}: {{ $tours - $publishedTours }}</small>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.destinations.index') }}">
                <span>{{ __('admin.destinations') }}</span>
                <strong>{{ $publishedDestinations }}/{{ $destinations }}</strong>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.hotels.index') }}">
                <span>{{ __('admin.hotels') }}</span>
                <strong>{{ $publishedHotels }}/{{ $hotels }}</strong>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.medical.services.index') }}">
                <span>{{ __('admin.medical_services') }}</span>
                <strong>{{ $publishedMedical }}/{{ $medicalServices }}</strong>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.attractions.index') }}">
                <span>{{ __('admin.attractions') }}</span>
                <strong>{{ $publishedAttractions }}/{{ $attractions }}</strong>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.inquiries.index') }}">
                <span>{{ __('admin.inquiries') }}</span>
                <strong>{{ $openInquiries }}/{{ $inquiries }}</strong>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.medical.inquiries.index') }}">
                <span>{{ __('admin.medical_inquiries') }}</span>
                <strong>{{ $openMedical }}/{{ $medicalInquiries }}</strong>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.feedback.index', ['status' => 'pending']) }}">
                <span>{{ __('admin.pending_feedback') }}</span>
                <strong>{{ $pendingReviews }}</strong>
            </a>
        </li>
    </ul>
    <h2>{{ __('admin.recent_inquiries') }}</h2>
    @if ($recent->isEmpty() && $recentMedical->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <ul class="k-admin-list">
            @foreach ($recent as $inquiry)
                <li><a href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }} — {{ __('admin.statuses.'.$inquiry->status) }}</a></li>
            @endforeach
            @foreach ($recentMedical as $inquiry)
                <li><a href="{{ route('admin.medical.inquiries.show', $inquiry) }}">{{ $inquiry->name }} — {{ __('admin.medical_inquiries') }} — {{ __('admin.statuses.'.$inquiry->status) }}</a></li>
            @endforeach
        </ul>
    @endif
@endsection
