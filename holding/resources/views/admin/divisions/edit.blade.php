@extends('layouts.admin')

@section('title', $division->translated()?->title ?: $division->key)

@section('content')
    <p><a href="{{ route('admin.divisions.index') }}">{{ __('admin.divisions') }}</a></p>
    <h1>{{ $division->translated()?->title ?: $division->key }}</h1>
    <form class="k-form" method="post" action="{{ route('admin.divisions.update', $division) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="k-field">
            <label for="position">{{ __('admin.position') }}</label>
            <input id="position" name="position" type="number" min="1" max="99" value="{{ old('position', $division->position) }}" required>
        </div>
        <label class="k-check">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $division->is_published ? '1' : '0'), FILTER_VALIDATE_BOOL))>
            <span>{{ __('admin.published') }}</span>
        </label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        <div class="k-field {{ $errors->has('destination_url') ? 'is-invalid' : '' }}">
            <label for="destination_url">{{ __('admin.destination') }}</label>
            <input id="destination_url" name="destination_url" value="{{ old('destination_url', $division->destination_url) }}">
            @if ($division->key === 'tourism')
                <p class="k-hint">{{ __('admin.tourism_destination') }}</p>
            @endif
        </div>
        <div class="k-field">
            <label for="image">{{ __('admin.image') }}</label>
            @if ($division->image_path)
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($division->image_path) }}" alt="">
            @endif
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($division->image_path)
                <label class="k-check"><input type="checkbox" name="remove_image" value="1"> {{ __('admin.remove_image') }}</label>
            @endif
        </div>
        @include('admin.partials.translation-tabs', [
            'rows' => $division->translations->keyBy('locale'),
            'fields' => [
                'title' => ['admin.title', false],
                'slug' => ['admin.slug', false],
                'introduction' => ['admin.introduction', true],
                'description' => ['admin.description', true],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
