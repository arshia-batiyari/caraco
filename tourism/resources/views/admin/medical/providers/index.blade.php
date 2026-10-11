@extends('layouts.admin')

@section('title', __('admin.clinics'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.clinics'),
        'create' => route('admin.medical.providers.create'),
        'edit' => 'admin.medical.providers.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
