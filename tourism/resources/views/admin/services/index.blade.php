@extends('layouts.admin')

@section('title', __('admin.services'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.services'),
        'create' => route('admin.services.create'),
        'edit' => 'admin.services.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
