@extends('layouts.admin')

@section('title', __('admin.faqs'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.faqs'),
        'create' => route('admin.faqs.create'),
        'edit' => 'admin.faqs.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->question
            ?? $record->translations->first()?->question
            ?? '#'.$record->id,
    ])
@endsection
