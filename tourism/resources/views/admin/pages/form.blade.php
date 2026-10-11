@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.pages.index') }}">{{ __('admin.pages') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->title ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.pages.update', $record) : route('admin.pages.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="key">{{ __('admin.key') }}</label>
            <input id="key" name="key" value="{{ old('key', $record->key) }}">
            <p class="k-hint">{{ __('admin.privacy_hint') }}</p>
        </div>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        @include('admin.partials.translation-tabs', ['rows' => $record->translations->keyBy('locale'), 'fields' => ['title' => ['admin.title', false], 'slug' => ['admin.slug', false], 'body' => ['admin.body', true], 'meta_title' => ['admin.meta_title', false], 'meta_description' => ['admin.meta_description', true], 'canonical_url' => ['admin.canonical', false]]])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.pages.destroy', $record) }}">@csrf @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label><button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
