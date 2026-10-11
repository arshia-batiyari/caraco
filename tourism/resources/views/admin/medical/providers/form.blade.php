@extends('layouts.admin')

@section('content')
    <p><a href="{{ route('admin.medical.providers.index') }}">{{ __('admin.clinics') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->name ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" enctype="multipart/form-data" action="{{ $record->exists ? route('admin.medical.providers.update', $record) : route('admin.medical.providers.store') }}">
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
        <fieldset>
            <legend>{{ __('admin.medical_services') }}</legend>
            @php $selected = collect(old('service_ids', $record->exists ? $record->services->pluck('id')->all() : []))->map(fn ($id) => (string) $id); @endphp
            @foreach ($services as $service)
                <label class="k-check">
                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked($selected->contains((string) $service->id))>
                    <span>{{ $service->translations->first()?->name ?? '#'.$service->id }}</span>
                </label>
            @endforeach
        </fieldset>
        <div class="k-field"><label for="position">{{ __('admin.position') }}</label><input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}"></div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
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
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.medical.providers.destroy', $record) }}">@csrf @method('DELETE')<button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
