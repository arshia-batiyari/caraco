@extends('layouts.admin')

@section('title', __('admin.attractions'))

@section('content')
    <p><a href="{{ route('admin.attraction-categories.index') }}">{{ __('admin.attraction_categories') }}</a></p>
    @include('admin.partials.index', [
        'heading' => __('admin.attractions'),
        'create' => route('admin.attractions.create'),
        'edit' => 'admin.attractions.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->title
            ?? $record->translations->first()?->title
            ?? '#'.$record->id,
    ])
@endsection
