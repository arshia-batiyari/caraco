@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.styles.index') }}">{{ __('admin.styles') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.styles.update', $record) : route('admin.styles.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        @include('admin.partials.translation-tabs', ['rows' => $record->translations->keyBy('locale'), 'fields' => ['name' => ['admin.name', false], 'slug' => ['admin.slug', false], 'summary' => ['admin.summary', true]]])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.styles.destroy', $record) }}">@csrf @method('DELETE')<button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
