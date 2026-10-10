@extends('layouts.admin')

@section('title', $page->exists ? ($page->translated()?->title ?: __('admin.edit')) : __('admin.create'))

@section('content')
    <p><a href="{{ route('admin.pages.index') }}">{{ __('admin.pages') }}</a></p>
    <h1>{{ $page->exists ? ($page->translated()?->title ?: ($page->key ?: __('admin.edit'))) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $page->exists ? route('admin.pages.update', $page) : route('admin.pages.store') }}">
        @csrf
        @if ($page->exists)
            @method('PUT')
        @endif
        <div class="k-field">
            <label for="position">{{ __('admin.position') }}</label>
            <input id="position" name="position" type="number" min="0" max="999" value="{{ old('position', $page->position ?? 0) }}" required>
        </div>
        <label class="k-check">
            <input type="hidden" name="is_published" value="0">
            <input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $page->is_published ? '1' : '0'), FILTER_VALIDATE_BOOL))>
            <span>{{ __('admin.published') }}</span>
        </label>
        @include('admin.partials.translation-tabs', [
            'rows' => $page->relationLoaded('translations') ? $page->translations->keyBy('locale') : collect(),
            'fields' => [
                'title' => ['admin.title', false],
                'slug' => ['admin.slug', false],
                'body' => ['admin.body', true],
                'meta_title' => ['admin.meta_title', false],
                'meta_description' => ['admin.meta_description', true],
                'canonical_url' => ['admin.canonical', false],
            ],
        ])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($page->exists && $page->key !== 'about')
        <form method="post" action="{{ route('admin.pages.destroy', $page) }}" class="k-admin-delete">
            @csrf
            @method('DELETE')
            <button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button>
        </form>
    @endif
@endsection
