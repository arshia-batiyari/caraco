@extends('layouts.site')

@section('header_tone', 'k-header-overlay')

@section('title', __('pages.home.title'))

@section('content')
    <x-hero
        variant="immersive"
        :badge="__('ui.placeholder')"
        :eyebrow="__('pages.home.eyebrow')"
        :title="__('pages.home.headline')"
        :lede="__('pages.home.lede')"
        image="alborz"
        :image-alt="__('pages.home.image_alt')"
    >
        <x-slot:actions>
            <a class="k-btn k-btn-gold" href="{{ route('journey') }}">{{ __('ui.nav.journey') }}</a>
            <a class="k-btn k-btn-line" href="{{ route('inquiry') }}">{{ __('ui.nav.inquiry') }}</a>
        </x-slot:actions>
    </x-hero>

    <section class="k-section">
        <div class="k-wrap k-split">
            <div>
                <x-section-heading :title="__('pages.home.statement_title')" />
                <div class="k-prose">
                    <p>{{ __('pages.home.statement') }}</p>
                    <p>{{ __('pages.home.statement_aside') }}</p>
                </div>
            </div>
            <x-photo name="bridge" :alt="__('pages.home.bridge_alt')" :caption="__('pages.home.bridge_caption')" />
        </div>
    </section>

    <section class="k-section k-section-tight">
        <div class="k-wrap">
            <x-section-heading
                :eyebrow="__('pages.home.gallery_eyebrow')"
                :title="__('pages.home.gallery_title')"
                :intro="__('pages.home.gallery_intro')"
            />
            <x-gallery :names="['desert', 'forest', 'columns']" :captions="__('pages.home.gallery')" />
        </div>
    </section>

    <x-cta
        :eyebrow="__('pages.home.cta_eyebrow')"
        :title="__('pages.home.cta_title')"
        :text="__('pages.home.cta_text')"
        :href="route('inquiry')"
        :label="__('pages.home.cta_label')"
    />
@endsection
