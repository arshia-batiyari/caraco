@extends('layouts.site')

@section('title', __('pages.journey.title'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('pages.journey.title')],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-badge>{{ __('ui.placeholder') }}</x-badge>
            <x-section-heading
                level="1"
                :eyebrow="__('pages.journey.eyebrow')"
                :title="__('pages.journey.headline')"
                :intro="__('pages.journey.lede')"
            />
            <div class="k-split">
                <ol class="k-steps">
                    @foreach ($days as $day)
                        <li>
                            <p class="k-eyebrow">{{ $day['label'] }}</p>
                            <h2>{{ $day['title'] }}</h2>
                            <p>{{ $day['text'] }}</p>
                        </li>
                    @endforeach
                </ol>
                <x-photo name="forest" :alt="__('pages.journey.image_alt')" :caption="__('pages.journey.image_caption')" />
            </div>
        </div>
    </section>

    <x-photo
        name="desert"
        variant="wide"
        :alt="__('pages.journey.wide_alt')"
        :caption="__('pages.journey.wide_caption')"
    />

    <x-cta
        :eyebrow="__('pages.home.cta_eyebrow')"
        :title="__('pages.home.cta_title')"
        :text="__('pages.home.cta_text')"
        :href="route('inquiry')"
        :label="__('pages.home.cta_label')"
    />
@endsection
