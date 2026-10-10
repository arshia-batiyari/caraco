@extends('layouts.admin')

@php
    $facilities = $record->exists ? $record->facilities : collect();
    $images = $record->exists ? $record->images : collect();
@endphp

@section('content')
    <p><a href="{{ route('admin.hotels.index') }}">{{ __('admin.hotels') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.hotels.update', $record) : route('admin.hotels.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="destination-id">{{ __('admin.destination') }}</label>
            <select id="destination-id" name="destination_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) old('destination_id', $record->destination_id) === (string) $destination->id)>{{ $destination->translations->first()?->name ?? '#'.$destination->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field"><label for="category">{{ __('admin.accommodation_category') }}</label><input id="category" name="accommodation_category" value="{{ old('accommodation_category', $record->accommodation_category) }}"></div>
        <div class="k-admin-grid">
            <div class="k-field"><label for="latitude">{{ __('admin.latitude') }}</label><input id="latitude" name="latitude" value="{{ old('latitude', $record->latitude) }}"></div>
            <div class="k-field"><label for="longitude">{{ __('admin.longitude') }}</label><input id="longitude" name="longitude" value="{{ old('longitude', $record->longitude) }}"></div>
        </div>
        <div class="k-field"><label for="website">{{ __('admin.website') }}</label><input id="website" name="website" value="{{ old('website', $record->website) }}"></div>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <label class="k-check"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(filter_var(old('is_featured', $record->is_featured), FILTER_VALIDATE_BOOL))><span>{{ __('admin.featured') }}</span></label>
        @include('admin.partials.translation-tabs', ['rows' => $record->translations->keyBy('locale'), 'fields' => [
            'name' => ['admin.name', false],
            'slug' => ['admin.slug', false],
            'summary' => ['admin.summary', true],
            'description' => ['admin.description', true],
            'address' => ['admin.address', true],
            'room_details' => ['admin.room_details', true],
            'meta_title' => ['admin.meta_title', false],
            'meta_description' => ['admin.meta_description', true],
            'canonical_url' => ['admin.canonical', false],
        ]])
        <fieldset class="k-admin-set">
            <legend>{{ __('admin.facilities') }}</legend>
            @foreach ($facilities->values()->concat([new \App\Models\HotelFacility, new \App\Models\HotelFacility]) as $index => $facility)
                @include('admin.partials.translation-tabs', [
                    'namePrefix' => 'facilities['.$index.'][translations]',
                    'rows' => $facility->exists ? $facility->translations->keyBy('locale') : collect(),
                    'fields' => ['name' => ['admin.name', false]],
                ])
            @endforeach
        </fieldset>
        <div class="k-field">
            <label for="images">{{ __('admin.images') }}</label>
            <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
        </div>
        @foreach ($images as $image)
            <label class="k-check">
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($image->path) }}" alt="">
                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                <span>{{ __('admin.remove') }}</span>
            </label>
        @endforeach
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.hotels.destroy', $record) }}">@csrf @method('DELETE')<button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
