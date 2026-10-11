@extends('layouts.admin')

@section('title', __('admin.doctors'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.doctors'),
        'create' => route('admin.medical.doctors.create'),
        'edit' => 'admin.medical.doctors.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
