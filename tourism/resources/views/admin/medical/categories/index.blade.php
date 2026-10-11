@extends('layouts.admin')
@section('title', __('admin.medical_categories'))
@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.medical_categories'),
        'create' => route('admin.medical.categories.create'),
        'edit' => 'admin.medical.categories.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
