@extends('layouts.admin')

@section('title', __('admin.dashboard'))

@section('content')
    <h1>{{ __('admin.dashboard') }}</h1>
    <p class="k-admin-lead">{{ __('admin.dashboard_lead') }}</p>
    <ul class="k-admin-stats">
        <li>
            <a href="{{ route('admin.divisions.index') }}">
                <span>{{ __('admin.divisions') }}</span>
                <strong>{{ $publishedDivisions }}/{{ $divisions }}</strong>
                <small>{{ __('admin.drafts') }}: {{ $divisions - $publishedDivisions }}</small>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.projects.index') }}">
                <span>{{ __('admin.projects') }}</span>
                <strong>{{ $publishedProjects }}/{{ $projects }}</strong>
                <small>{{ __('admin.drafts') }}: {{ $projects - $publishedProjects }}</small>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.pages.index') }}">
                <span>{{ __('admin.pages') }}</span>
                <strong>{{ $publishedPages }}/{{ $pages }}</strong>
                <small>{{ __('admin.drafts') }}: {{ $pages - $publishedPages }}</small>
            </a>
        </li>
        <li>
            <a href="{{ route('admin.inquiries.index') }}">
                <span>{{ __('admin.inquiries') }}</span>
                <strong>{{ $inquiries }}</strong>
            </a>
        </li>
    </ul>
    <h2>{{ __('admin.recent_inquiries') }}</h2>
    @if ($recent->isEmpty())
        <x-empty-state :title="__('admin.empty')" />
    @else
        <ul class="k-admin-list">
            @foreach ($recent as $inquiry)
                <li><a href="{{ route('admin.inquiries.show', $inquiry) }}">{{ $inquiry->name }} — {{ $inquiry->email }}</a></li>
            @endforeach
        </ul>
    @endif
@endsection
