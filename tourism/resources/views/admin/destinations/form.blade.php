@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.destinations.index') }}">{{ __('admin.destinations') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.destinations.update', $record) : route('admin.destinations.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="type">{{ __('admin.type') }}</label>
            <select id="type" name="type">
                @foreach (['country', 'region', 'city'] as $type)
                    <option value="{{ $type }}" @selected(old('type', $record->type) === $type)>{{ __('admin.destination_types.'.$type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="parent-id">{{ __('admin.parent') }}</label>
            <select id="parent-id" name="parent_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $record->parent_id) === (string) $parent->id)>{{ $parent->translations->first()?->name ?? '#'.$parent->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="position">{{ __('admin.position') }}</label>
            <input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}">
        </div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        <label class="k-check"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(filter_var(old('is_featured', $record->is_featured), FILTER_VALIDATE_BOOL))><span>{{ __('admin.featured') }}</span></label>
        <div class="k-field">
            <label for="image">{{ __('admin.image') }}</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($record->image_path)
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($record->image_path) }}" alt="">
                <label class="k-check"><input type="checkbox" name="remove_image" value="1"><span>{{ __('admin.remove') }}</span></label>
            @endif
        </div>
        @include('admin.partials.translation-tabs', [
            'rows' => $record->translations->keyBy('locale'),
            'fields' => [
                'name' => ['admin.name', false],
                'slug' => ['admin.slug', false],
                'summary' => ['admin.summary', true],
                'description' => ['admin.description', true],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.destinations.destroy', $record) }}">
            @csrf
            @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label>
            <button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button>
        </form>
    @endif
@endsection
