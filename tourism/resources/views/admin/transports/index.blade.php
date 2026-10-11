@extends('layouts.admin')

@section('title', __('admin.transports'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.transports'),
        'create' => route('admin.transports.create'),
        'edit' => 'admin.transports.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
