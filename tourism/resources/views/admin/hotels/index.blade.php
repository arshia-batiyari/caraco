@extends('layouts.admin')

@section('title', __('admin.hotels'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.hotels'),
        'create' => route('admin.hotels.create'),
        'edit' => 'admin.hotels.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
