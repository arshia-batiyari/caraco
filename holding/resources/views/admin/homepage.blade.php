@extends('layouts.admin')

@section('title', __('admin.homepage'))

@section('content')
    <h1>{{ __('admin.homepage') }}</h1>
    <form class="k-form" method="post" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.video') }}</legend>
            @foreach (['none' => 'admin.video_none', 'file' => 'admin.video_file', 'url' => 'admin.video_url'] as $value => $label)
                <label class="k-check">
                    <input type="radio" name="video_type" value="{{ $value }}" @checked(old('video_type', $videoType) === $value)>
                    <span>{{ __($label) }}</span>
                </label>
            @endforeach
            <div class="k-field {{ $errors->has('video_url') ? 'is-invalid' : '' }}">
                <label for="video_url">{{ __('admin.video_url') }}</label>
                <input id="video_url" name="video_url" value="{{ old('video_url', $videoUrl) }}" placeholder="https://www.youtube.com/watch?v=">
                @if ($errors->has('video_url'))
                    <p class="k-field-error">{{ $errors->first('video_url') }}</p>
                @endif
            </div>
            <div class="k-field {{ $errors->has('video') ? 'is-invalid' : '' }}">
                <label for="video">{{ __('admin.video_file') }}</label>
                @if ($videoPath)
                    <p>{{ $videoPath }}</p>
                @endif
                <input id="video" name="video" type="file" accept="video/mp4">
                @if ($errors->has('video'))
                    <p class="k-field-error">{{ $errors->first('video') }}</p>
                @endif
                @if ($videoPath)
                    <label class="k-check"><input type="checkbox" name="remove_video" value="1"> {{ __('admin.remove_file') }}</label>
                @endif
            </div>
            <div class="k-field">
                <label for="poster">{{ __('admin.poster') }}</label>
                @if ($posterPath)
                    <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($posterPath) }}" alt="">
                @endif
                <input id="poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp">
                @if ($posterPath)
                    <label class="k-check"><input type="checkbox" name="remove_poster" value="1"> {{ __('admin.remove_image') }}</label>
                @endif
            </div>
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.images') }}</legend>
            <div class="k-field">
                <label for="hero_image">{{ __('admin.hero_image') }}</label>
                @if ($heroImage)
                    <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($heroImage) }}" alt="">
                @endif
                <input id="hero_image" name="hero_image" type="file" accept="image/jpeg,image/png,image/webp">
                @if ($heroImage)
                    <label class="k-check"><input type="checkbox" name="remove_hero" value="1"> {{ __('admin.remove_image') }}</label>
                @endif
            </div>
            <div class="k-field">
                <label for="intro_image">{{ __('admin.intro_image') }}</label>
                @if ($introImage)
                    <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($introImage) }}" alt="">
                @endif
                <input id="intro_image" name="intro_image" type="file" accept="image/jpeg,image/png,image/webp">
                @if ($introImage)
                    <label class="k-check"><input type="checkbox" name="remove_intro" value="1"> {{ __('admin.remove_image') }}</label>
                @endif
            </div>
        </fieldset>

        <div data-locale-tabs>
            <div class="k-tabs" role="tablist">
                @foreach (config('karako.locales') as $code => $meta)
                    <button type="button" role="tab" data-locale-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $meta['native'] }}</button>
                @endforeach
            </div>
            @foreach (config('karako.locales') as $code => $meta)
                @php $row = $translations->get($code); @endphp
                <div class="k-locale-panel" data-locale-panel="{{ $code }}" @if (! $loop->first) hidden @endif>
                    @foreach ([
                        'hero_eyebrow' => 'admin.hero_eyebrow',
                        'hero_title' => 'admin.hero_title',
                        'divisions_heading' => 'admin.divisions_heading',
                        'intro_heading' => 'admin.intro_heading',
                        'approach_heading' => 'admin.approach_heading',
                        'projects_heading' => 'admin.projects_heading',
                        'cta_heading' => 'admin.cta_heading',
                        'meta_title' => 'admin.meta_title',
                    ] as $field => $label)
                        <div class="k-field">
                            <label for="{{ $field }}-{{ $code }}">{{ __($label) }}</label>
                            <input id="{{ $field }}-{{ $code }}" name="translations[{{ $code }}][{{ $field }}]" value="{{ old('translations.'.$code.'.'.$field, $row?->{$field}) }}">
                        </div>
                    @endforeach
                    @foreach ([
                        'hero_lede' => 'admin.hero_lede',
                        'intro_body' => 'admin.intro_body',
                        'approach_body' => 'admin.approach_body',
                        'cta_text' => 'admin.cta_text',
                        'meta_description' => 'admin.meta_description',
                    ] as $field => $label)
                        <div class="k-field">
                            <label for="{{ $field }}-{{ $code }}">{{ __($label) }}</label>
                            <textarea id="{{ $field }}-{{ $code }}" name="translations[{{ $code }}][{{ $field }}]" rows="4">{{ old('translations.'.$code.'.'.$field, $row?->{$field}) }}</textarea>
                        </div>
                    @endforeach
                    <div class="k-field {{ $errors->has('translations.'.$code.'.canonical_url') ? 'is-invalid' : '' }}">
                        <label for="canonical-{{ $code }}">{{ __('admin.canonical') }}</label>
                        <input id="canonical-{{ $code }}" name="translations[{{ $code }}][canonical_url]" value="{{ old('translations.'.$code.'.canonical_url', $row?->canonical_url) }}">
                    </div>
                </div>
            @endforeach
        </div>

        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
