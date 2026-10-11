@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.medical.services.index') }}">{{ __('admin.medical_services') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.medical.services.update', $record) : route('admin.medical.services.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="category">{{ __('admin.medical_categories') }}</label>
            <select id="category" name="medical_category_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('medical_category_id', $record->medical_category_id) === (string) $category->id)>{{ $category->translations->firstWhere('locale', 'fa')?->name ?? $category->translations->first()?->name ?? '#'.$category->id }}</option>
                @endforeach
            </select>
        </div>
        <fieldset>
            <legend>{{ __('admin.clinics') }}</legend>
            @php $selected = collect(old('provider_ids', $record->exists ? $record->providers->pluck('id')->all() : []))->map(fn ($id) => (string) $id); @endphp
            @foreach ($providers as $provider)
                <label class="k-check">
                    <input type="checkbox" name="provider_ids[]" value="{{ $provider->id }}" @checked($selected->contains((string) $provider->id))>
                    <span>{{ $provider->translations->firstWhere('locale', 'fa')?->name ?? $provider->translations->first()?->name ?? '#'.$provider->id }}</span>
                </label>
            @endforeach
        </fieldset>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        <div class="k-field">
            <label for="cover">{{ __('admin.cover') }}</label>
            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($record->cover_path)
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($record->cover_path) }}" alt="">
                <label class="k-check"><input type="checkbox" name="remove_cover" value="1"><span>{{ __('admin.remove_cover') }}</span></label>
            @endif
        </div>
        @include('admin.partials.translation-tabs', [
            'rows' => $record->exists ? $record->translations->keyBy('locale') : collect(),
            'fields' => [
                'name' => ['admin.name', false],
                'slug' => ['admin.slug', false],
                'summary' => ['admin.summary', true],
                'description' => ['admin.description', true],
                'cover_alt' => ['admin.cover_alt', false],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        @include('admin.partials.gallery-fields', ['images' => $record->exists ? $record->images : collect()])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.medical.services.destroy', $record) }}">@csrf @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label><button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
