@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.services.index') }}">{{ __('admin.services') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.services.update', $record) : route('admin.services.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="type">{{ __('admin.type') }}</label>
            <select id="type" name="type">
                <option value="included" @selected(old('type', $record->type) === 'included')>{{ __('ui.tours.included') }}</option>
                <option value="excluded" @selected(old('type', $record->type) === 'excluded')>{{ __('ui.tours.excluded') }}</option>
            </select>
        </div>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        @include('admin.partials.translation-tabs', ['rows' => $record->translations->keyBy('locale'), 'fields' => ['name' => ['admin.name', false], 'description' => ['admin.description', true]]])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.services.destroy', $record) }}">@csrf @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label><button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
