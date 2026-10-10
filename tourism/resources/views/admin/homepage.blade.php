@extends('layouts.admin')

@section('title', __('admin.homepage'))

@section('content')
    <h1>{{ __('admin.homepage') }}</h1>
    <form class="k-form" method="post" action="{{ route('admin.homepage.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="k-field">
            <label for="hero-image">{{ __('admin.hero_image') }}</label>
            <input id="hero-image" name="hero_image" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($heroImage)
                <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($heroImage) }}" alt="">
                <label class="k-check"><input type="checkbox" name="remove_hero" value="1"><span>{{ __('admin.remove') }}</span></label>
            @endif
        </div>
        <div class="k-field">
            <label for="video-type">{{ __('admin.video') }}</label>
            <select id="video-type" name="video_type">
                @foreach (['none', 'url', 'file'] as $type)
                    <option value="{{ $type }}" @selected(old('video_type', $videoType) === $type)>{{ __('admin.video_types.'.$type) }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-field">
            <label for="video-url">{{ __('admin.video_url') }}</label>
            <input id="video-url" name="video_url" value="{{ old('video_url', $videoUrl) }}">
        </div>
        <div class="k-field">
            <label for="video">{{ __('admin.video_file') }}</label>
            <input id="video" name="video" type="file" accept="video/mp4">
            @if ($videoPath)
                <label class="k-check"><input type="checkbox" name="remove_video" value="1"><span>{{ __('admin.remove') }}</span></label>
            @endif
        </div>
        <div class="k-field">
            <label for="poster">{{ __('admin.poster') }}</label>
            <input id="poster" name="poster" type="file" accept="image/jpeg,image/png,image/webp">
            @if ($posterPath)
                <label class="k-check"><input type="checkbox" name="remove_poster" value="1"><span>{{ __('admin.remove') }}</span></label>
            @endif
        </div>
        <div class="k-admin-grid">
            <div class="k-field"><label for="contact-email">{{ __('ui.fields.email') }}</label><input id="contact-email" name="contact_email" type="email" value="{{ old('contact_email', $contactEmail) }}"></div>
            <div class="k-field"><label for="contact-phone">{{ __('ui.fields.phone') }}</label><input id="contact-phone" name="contact_phone" value="{{ old('contact_phone', $contactPhone) }}"></div>
        </div>
        @include('admin.partials.translation-tabs', ['rows' => $translations, 'fields' => [
            'hero_eyebrow' => ['admin.hero_eyebrow', false],
            'hero_title' => ['admin.hero_title', false],
            'hero_lede' => ['admin.hero_lede', true],
            'intro_heading' => ['admin.intro_heading', false],
            'intro_body' => ['admin.intro_body', true],
            'destinations_heading' => ['admin.destinations_heading', false],
            'categories_heading' => ['admin.categories_heading', false],
            'popular_heading' => ['admin.popular_heading', false],
            'discounted_heading' => ['admin.discounted_heading', false],
            'domestic_heading' => ['admin.domestic_heading', false],
            'international_heading' => ['admin.international_heading', false],
            'experiences_heading' => ['admin.experiences_heading', false],
            'medical_heading' => ['admin.medical_heading', false],
            'medical_body' => ['admin.medical_body', true],
            'cta_heading' => ['admin.cta_heading', false],
            'cta_text' => ['admin.cta_text', true],
            'meta_title' => ['admin.meta_title', false],
            'meta_description' => ['admin.meta_description', true],
            'canonical_url' => ['admin.canonical', false],
        ]])
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
