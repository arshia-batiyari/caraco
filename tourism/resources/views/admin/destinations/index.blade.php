@extends('layouts.admin')

@section('title', __('admin.destinations'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.destinations'),
        'create' => route('admin.destinations.create'),
        'edit' => 'admin.destinations.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
