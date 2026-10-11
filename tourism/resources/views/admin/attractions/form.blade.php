@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.attractions.index') }}">{{ __('admin.attractions') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->title ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.attractions.update', $record) : route('admin.attractions.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="destination">{{ __('admin.destinations') }}</label>
            <select id="destination" name="destination_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) old('destination_id', $record->destination_id) === (string) $destination->id)>{{ $destination->translations->first()?->name ?? '#'.$destination->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="category">{{ __('admin.attraction_categories') }}</label>
            <select id="category" name="attraction_category_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('attraction_category_id', $record->attraction_category_id) === (string) $category->id)>{{ $category->translations->first()?->name ?? '#'.$category->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="classification">{{ __('admin.classification') }}</label>
            <select id="classification" name="classification">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach (['cultural', 'natural', 'mixed'] as $kind)
                    <option value="{{ $kind }}" @selected(old('classification', $record->classification) === $kind)>{{ __('ui.attractions.classifications.'.$kind) }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="unesco">{{ __('admin.unesco_designation') }}</label>
            <select id="unesco" name="unesco_designation">
                <option value="none" @selected(old('unesco_designation', $record->unesco_designation ?: 'none') === 'none')>{{ __('admin.unesco_none') }}</option>
                <option value="world_heritage" @selected(old('unesco_designation', $record->unesco_designation) === 'world_heritage')>{{ __('admin.unesco_world_heritage') }}</option>
            </select>
        </div>
        <label class="k-check">
            <input type="hidden" name="unesco_verified" value="0">
            <input type="checkbox" name="unesco_verified" value="1" @checked(filter_var(old('unesco_verified', $record->unesco_verified), FILTER_VALIDATE_BOOL))>
            <span>{{ __('admin.unesco_verified') }}</span>
        </label>
        <p class="k-notice">{{ __('admin.unesco_hint') }}</p>
        <div class="k-field">
            <label for="inscribed">{{ __('admin.inscribed_on') }}</label>
            <input id="inscribed" name="inscribed_on" type="date" value="{{ old('inscribed_on', $record->inscribed_on?->toDateString()) }}">
        </div>
        <div class="k-field">
            <label for="reference">{{ __('admin.unesco_reference') }}</label>
            <input id="reference" name="unesco_reference" value="{{ old('unesco_reference', $record->unesco_reference) }}" maxlength="40">
        </div>
        <div class="k-field">
            <label for="latitude">{{ __('admin.latitude') }}</label>
            <input id="latitude" name="latitude" value="{{ old('latitude', $record->latitude) }}">
        </div>
        <div class="k-field">
            <label for="longitude">{{ __('admin.longitude') }}</label>
            <input id="longitude" name="longitude" value="{{ old('longitude', $record->longitude) }}">
        </div>
        <fieldset>
            <legend>{{ __('admin.related_tours') }}</legend>
            @php $selected = collect(old('tour_ids', $record->exists ? $record->tours->pluck('id')->all() : []))->map(fn ($id) => (string) $id); @endphp
            @foreach ($tours as $tour)
                <label class="k-check">
                    <input type="checkbox" name="tour_ids[]" value="{{ $tour->id }}" @checked($selected->contains((string) $tour->id))>
                    <span>{{ $tour->translations->first()?->title ?? '#'.$tour->id }}</span>
                </label>
            @endforeach
        </fieldset>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <label class="k-check"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(filter_var(old('is_featured', $record->is_featured), FILTER_VALIDATE_BOOL))><span>{{ __('admin.featured') }}</span></label>
        <div class="k-field">
            <label for="cover">{{ __('admin.cover') }}</label>
            <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($record->cover_path)
                <label class="k-check"><input type="checkbox" name="remove_cover" value="1"><span>{{ __('admin.remove_cover') }}</span></label>
            @endif
        </div>
        @include('admin.partials.translation-tabs', [
            'rows' => $record->exists ? $record->translations->keyBy('locale') : collect(),
            'fields' => [
                'title' => ['admin.title', false],
                'slug' => ['admin.slug', false],
                'summary' => ['admin.summary', true],
                'description' => ['admin.description', true],
                'visitor_info' => ['admin.visitor_info', true],
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
        <form class="k-admin-delete" method="post" action="{{ route('admin.attractions.destroy', $record) }}">@csrf @method('DELETE')<button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
