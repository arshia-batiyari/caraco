@extends('layouts.admin')

@section('title', __('admin.styles'))

@section('content')
    @include('admin.partials.index', [
        'heading' => __('admin.styles'),
        'create' => route('admin.styles.create'),
        'edit' => 'admin.styles.edit',
        'records' => $records,
        'label' => fn ($record) => $record->translations->firstWhere('locale', 'fa')?->name
            ?? $record->translations->first()?->name
            ?? '#'.$record->id,
    ])
@endsection
