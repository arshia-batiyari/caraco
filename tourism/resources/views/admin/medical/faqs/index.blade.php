@extends('layouts.admin')
@section('title', __('admin.medical_faqs'))
@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.medical_faqs'),
        'create' => route('admin.medical.faqs.create'),
        'edit' => 'admin.medical.faqs.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->question
            ?? $record->translations->first()?->question
            ?? '#'.$record->id,
    ])
@endsection
