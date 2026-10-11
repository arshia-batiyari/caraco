@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.medical.doctors.index') }}">{{ __('admin.doctors') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <p class="k-notice">{{ __('admin.doctor_note') }}</p>
    <form class="k-form" method="post" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.medical.doctors.update', $record) : route('admin.medical.doctors.store') }}">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-field">
            <label for="provider">{{ __('admin.clinics') }}</label>
            <select id="provider" name="medical_provider_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($providers as $provider)
                    <option value="{{ $provider->id }}" @selected((string) old('medical_provider_id', $record->medical_provider_id) === (string) $provider->id)>{{ $provider->translations->first()?->name ?? '#'.$provider->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <div class="k-field">
            <label for="image">{{ __('admin.cover') }}</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($record->image_path)
                <label class="k-check"><input type="checkbox" name="remove_image" value="1"><span>{{ __('admin.remove_cover') }}</span></label>
            @endif
        </div>
        @include('admin.partials.translation-tabs', [
            'rows' => $record->exists ? $record->translations->keyBy('locale') : collect(),
            'fields' => [
                'name' => ['admin.name', false],
                'slug' => ['admin.slug', false],
                'specialty' => ['admin.specialty', false],
                'biography' => ['admin.biography', true],
                'image_alt' => ['admin.image_alt', false],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.medical.doctors.destroy', $record) }}">@csrf @method('DELETE')<button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
