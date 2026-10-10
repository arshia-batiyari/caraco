@extends('layouts.site')

@section('title', __('pages.home.title'))

@section('content')
    <x-hero
        variant="editorial"
        :badge="__('ui.placeholder')"
        :eyebrow="__('pages.home.eyebrow')"
        :title="__('pages.home.headline')"
        :lede="__('pages.home.lede')"
        image="pavilion"
        :image-alt="__('pages.home.image_alt')"
    >
        <x-slot:actions>
            <a class="k-btn k-btn-ink" href="{{ route('divisions') }}">{{ __('ui.nav.divisions') }}</a>
            <a class="k-btn k-btn-line" href="{{ route('contact') }}">{{ __('ui.nav.contact') }}</a>
        </x-slot:actions>
    </x-hero>

    <section class="k-section">
        <div class="k-wrap k-split">
            <x-section-heading
                :eyebrow="__('pages.home.list_eyebrow')"
                :title="__('pages.home.statement_title')"
            />
            <div class="k-prose">
                <p>{{ __('pages.home.statement') }}</p>
                <p>{{ __('pages.home.statement_aside') }}</p>
            </div>
        </div>
    </section>

    <section class="k-section k-section-tight">
        <div class="k-wrap">
            <x-section-heading :title="__('pages.home.list_title')" />
            <div class="k-index">
                @foreach ($divisions as $index => $division)
                    <a class="k-index-link" href="{{ route('divisions') }}#{{ $division['slug'] }}">
                        <span class="k-index-no">{{ sprintf('%02d', $index + 1) }}</span>
                        <span class="k-index-title">{{ $division['title'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <x-cta
        :eyebrow="__('pages.home.cta_eyebrow')"
        :title="__('pages.home.cta_title')"
        :text="__('pages.home.cta_text')"
        :href="route('contact')"
        :label="__('pages.home.cta_label')"
    />
@endsection
