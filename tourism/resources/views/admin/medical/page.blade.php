@extends('layouts.admin')

@section('title', __('admin.medical_page'))

@section('content')
    <p><a href="{{ route('admin.medical.home') }}">{{ __('admin.medical') }}</a></p>
    <h1>{{ __('admin.medical_page') }}</h1>
    <p class="k-notice">{{ __('admin.medical_disclaimer_note') }}</p>
    <form class="k-form" method="post" action="{{ route('admin.medical.page.update') }}">
        @csrf
        @method('PUT')
        @include('admin.partials.translation-tabs', [
            'rows' => $record->exists ? $record->translations->keyBy('locale') : collect(),
            'fields' => [
                'title' => ['admin.title', false],
                'summary' => ['admin.summary', true],
                'body' => ['admin.body', true],
                'travel_assistance' => ['admin.travel_assistance', true],
                'stay_assistance' => ['admin.stay_assistance', true],
                'contact_note' => ['admin.contact_note', true],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
