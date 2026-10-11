@extends('layouts.admin')
@section('title', __('admin.medical_steps'))
@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.medical_steps'),
        'create' => route('admin.medical.steps.create'),
        'edit' => 'admin.medical.steps.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->title
            ?? $record->translations->first()?->title
            ?? '#'.$record->id,
    ])
@endsection
