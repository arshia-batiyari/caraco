@extends('layouts.admin')
@section('title', __('admin.medical'))
@section('content')
    <h1>{{ __('admin.medical') }}</h1>
    <ul class="k-inline-links">
        <li><a href="{{ route('admin.medical.page') }}">{{ __('admin.medical_page') }}</a></li>
        <li><a href="{{ route('admin.medical.categories.index') }}">{{ __('admin.medical_categories') }}</a></li>
        <li><a href="{{ route('admin.medical.services.index') }}">{{ __('admin.medical_services') }}</a></li>
        <li><a href="{{ route('admin.medical.providers.index') }}">{{ __('admin.clinics') }}</a></li>
        <li><a href="{{ route('admin.medical.doctors.index') }}">{{ __('admin.doctors') }}</a></li>
        <li><a href="{{ route('admin.medical.steps.index') }}">{{ __('admin.medical_steps') }}</a></li>
        <li><a href="{{ route('admin.medical.faqs.index') }}">{{ __('admin.medical_faqs') }}</a></li>
        <li><a href="{{ route('admin.medical.inquiries.index') }}">{{ __('admin.medical_inquiries') }}</a></li>
    </ul>
@endsection
