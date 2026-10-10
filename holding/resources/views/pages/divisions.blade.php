@extends('layouts.site')

@section('title', __('pages.divisions.title'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('pages.divisions.title')],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-badge>{{ __('ui.placeholder') }}</x-badge>
            <x-section-heading
                level="1"
                :eyebrow="__('pages.divisions.eyebrow')"
                :title="__('pages.divisions.headline')"
                :intro="__('pages.divisions.lede')"
            />

            <article class="k-feature" id="{{ $lead['slug'] }}">
                <x-photo name="columns" :alt="__('pages.divisions.image_alt')" :caption="__('pages.divisions.image_caption')" />
                <div class="k-prose">
                    <p class="k-eyebrow">01</p>
                    <h2>{{ $lead['title'] }}</h2>
                    <p>{{ $lead['text'] }}</p>
                </div>
            </article>

            <div class="k-index">
                @foreach ($rest as $index => $division)
                    <article class="k-index-row" id="{{ $division['slug'] }}">
                        <span class="k-index-no">{{ sprintf('%02d', $index + 2) }}</span>
                        <h3 class="k-index-title">{{ $division['title'] }}</h3>
                        <p class="k-index-text">{{ $division['text'] }}</p>
                    </article>
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
