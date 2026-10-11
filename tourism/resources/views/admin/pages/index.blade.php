@extends('layouts.admin')

@section('title', __('admin.pages'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.pages'),
        'create' => route('admin.pages.create'),
        'edit' => 'admin.pages.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->title
            ?? $record->translations->first()?->title
            ?? '#'.$record->id,
    ])
@endsection
