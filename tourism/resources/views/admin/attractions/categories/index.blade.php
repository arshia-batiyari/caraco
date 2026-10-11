@extends('layouts.admin')
@section('title', __('admin.attraction_categories'))
@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.attraction_categories'),
        'create' => route('admin.attraction-categories.create'),
        'edit' => 'admin.attraction-categories.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
