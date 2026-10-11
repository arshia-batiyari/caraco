@extends('layouts.admin')

@section('title', $project->exists ? ($project->translated()?->title ?: __('admin.edit')) : __('admin.create'))

@section('content')
    <p><a href="{{ route('admin.projects.index') }}">{{ __('admin.projects') }}</a></p>
    <h1>{{ $project->exists ? ($project->translated()?->title ?: __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($project->exists)
            @method('PUT')
        @endif
        <div class="k-field">
            <label for="position">{{ __('admin.position') }}</label>
            <input id="position" name="position" type="number" min="0" max="999" value="{{ old('position', $project->position ?? 0) }}" required>
        </div>
        <label class="k-check">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $project->is_published ? '1' : '0'), FILTER_VALIDATE_BOOL))>
            <span>{{ __('admin.published') }}</span>
        </label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        <div class="k-field">
            <label for="image">{{ __('admin.image') }}</label>
            @if ($project->image_path)
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($project->image_path) }}" alt="">
            @endif
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($project->image_path)
                <label class="k-check"><input type="checkbox" name="remove_image" value="1"> {{ __('admin.remove_image') }}</label>
            @endif
        </div>
        @include('admin.partials.translation-tabs', [
            'rows' => $project->relationLoaded('translations') ? $project->translations->keyBy('locale') : collect(),
            'fields' => [
                'title' => ['admin.title', false],
                'slug' => ['admin.slug', false],
                'summary' => ['admin.summary', true],
                'body' => ['admin.body', true],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($project->exists)
        <form method="post" action="{{ route('admin.projects.destroy', $project) }}" class="k-admin-delete">
            @csrf
            @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label>
            <button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button>
        </form>
    @endif
@endsection
