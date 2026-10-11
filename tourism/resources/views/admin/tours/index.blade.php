@extends('layouts.admin')

@section('title', __('admin.tours'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.tours'),
        'create' => route('admin.tours.create'),
        'edit' => 'admin.tours.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->title
            ?? $record->translations->first()?->title
            ?? '#'.$record->id,
    ])
@endsection
