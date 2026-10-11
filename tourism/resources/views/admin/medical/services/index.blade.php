@extends('layouts.admin')

@section('title', __('admin.medical_services'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.medical_services'),
        'create' => route('admin.medical.services.create'),
        'edit' => 'admin.medical.services.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
