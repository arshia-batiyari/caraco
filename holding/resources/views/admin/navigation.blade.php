@extends('layouts.admin')

@section('title', __('admin.navigation'))

@section('content')
    <h1>{{ __('admin.navigation') }}</h1>
    <form class="k-form" method="post" action="{{ route('admin.navigation.update') }}">
        @csrf
        @method('PUT')
        @foreach ($items as $item)
            <fieldset class="k-admin-set">
                <legend>{{ $item->location }} · {{ $item->kind }}</legend>
                <input type="hidden" name="items[{{ $item->id }}][location]" value="{{ $item->location }}">
                <div class="k-admin-grid">
                    <div class="k-field">
                        <label for="kind-{{ $item->id }}">{{ __('admin.kind') }}</label>
                        <select id="kind-{{ $item->id }}" name="items[{{ $item->id }}][kind]">
                            @foreach (['route', 'page', 'url'] as $kind)
                                <option value="{{ $kind }}" @selected(old('items.'.$item->id.'.kind', $item->kind) === $kind)>{{ __('admin.kinds.'.$kind) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="k-field">
                        <label for="route-{{ $item->id }}">{{ __('admin.route') }}</label>
                        <select id="route-{{ $item->id }}" name="items[{{ $item->id }}][route_name]">
                            <option value="">—</option>
                            @foreach (['home', 'about', 'divisions.index', 'projects.index', 'contact'] as $route)
                                <option value="{{ $route }}" @selected(old('items.'.$item->id.'.route_name', $item->route_name) === $route)>{{ $route }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="k-field">
                        <label for="page-{{ $item->id }}">{{ __('admin.pages') }}</label>
                        <select id="page-{{ $item->id }}" name="items[{{ $item->id }}][page_id]">
                            <option value="">—</option>
                            @foreach ($pages as $page)
                                <option value="{{ $page->id }}" @selected((string) old('items.'.$item->id.'.page_id', $item->page_id) === (string) $page->id)>{{ $page->translated()?->title ?: ($page->key ?: '#'.$page->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="k-field">
                        <label for="url-{{ $item->id }}">{{ __('admin.url') }}</label>
                        <input id="url-{{ $item->id }}" name="items[{{ $item->id }}][url]" value="{{ old('items.'.$item->id.'.url', $item->url) }}">
                    </div>
                    <div class="k-field">
                        <label for="position-{{ $item->id }}">{{ __('admin.position') }}</label>
                        <input id="position-{{ $item->id }}" name="items[{{ $item->id }}][position]" type="number" min="0" max="999" value="{{ old('items.'.$item->id.'.position', $item->position) }}">
                    </div>
                </div>
                <label class="k-check">
                    <input type="hidden" name="items[{{ $item->id }}][is_published]" value="0">
                    <input type="checkbox" name="items[{{ $item->id }}][is_published]" value="1" @checked(filter_var(old('items.'.$item->id.'.is_published', $item->is_published ? '1' : '0'), FILTER_VALIDATE_BOOL))>
                    <span>{{ __('admin.published') }}</span>
                </label>
                <label class="k-check">
                    <input type="checkbox" name="remove[]" value="{{ $item->id }}">
                    <span>{{ __('admin.delete') }}</span>
                </label>
                @foreach (config('karako.locales') as $code => $meta)
                    <div class="k-field">
                        <label for="label-{{ $item->id }}-{{ $code }}">{{ $meta['native'] }}</label>
                        <input id="label-{{ $item->id }}-{{ $code }}" name="items[{{ $item->id }}][labels][{{ $code }}]" value="{{ old('items.'.$item->id.'.labels.'.$code, $item->translations->firstWhere('locale', $code)?->label) }}">
                    </div>
                @endforeach
            </fieldset>
        @endforeach

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.add_link') }}</legend>
            <div class="k-admin-grid">
                <div class="k-field">
                    <label for="new-location">{{ __('admin.location') }}</label>
                    <select id="new-location" name="new[location]">
                        <option value="header">header</option>
                        <option value="footer">footer</option>
                    </select>
                </div>
                <div class="k-field">
                    <label for="new-kind">{{ __('admin.kind') }}</label>
                    <select id="new-kind" name="new[kind]">
                        <option value="">—</option>
                        @foreach (['route', 'page', 'url'] as $kind)
                            <option value="{{ $kind }}">{{ __('admin.kinds.'.$kind) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="new-route">{{ __('admin.route') }}</label>
                    <select id="new-route" name="new[route_name]">
                        <option value="">—</option>
                        @foreach (['home', 'about', 'divisions.index', 'projects.index', 'contact'] as $route)
                            <option value="{{ $route }}">{{ $route }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="new-page">{{ __('admin.pages') }}</label>
                    <select id="new-page" name="new[page_id]">
                        <option value="">—</option>
                        @foreach ($pages as $page)
                            <option value="{{ $page->id }}">{{ $page->translated()?->title ?: ($page->key ?: '#'.$page->id) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="new-url">{{ __('admin.url') }}</label>
                    <input id="new-url" name="new[url]" value="{{ old('new.url') }}">
                </div>
                <div class="k-field">
                    <label for="new-position">{{ __('admin.position') }}</label>
                    <input id="new-position" name="new[position]" type="number" min="0" max="999" value="{{ old('new.position', 0) }}">
                </div>
            </div>
            <input type="hidden" name="new[is_published]" value="0">
            <label class="k-check">
                <input type="checkbox" name="new[is_published]" value="1" checked>
                <span>{{ __('admin.published') }}</span>
            </label>
            @foreach (config('karako.locales') as $code => $meta)
                <div class="k-field">
                    <label for="new-label-{{ $code }}">{{ $meta['native'] }}</label>
                    <input id="new-label-{{ $code }}" name="new[labels][{{ $code }}]" value="{{ old('new.labels.'.$code) }}">
                </div>
            @endforeach
        </fieldset>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
