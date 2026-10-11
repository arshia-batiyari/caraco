@extends('layouts.admin')

@section('title', __('admin.categories'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.categories'),
        'create' => route('admin.categories.create'),
        'edit' => 'admin.categories.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
