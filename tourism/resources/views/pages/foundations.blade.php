@extends('layouts.site')

@section('title', __('ui.foundations.title'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('ui.foundations.title')],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading
                level="1"
                :eyebrow="__('ui.placeholder')"
                :title="__('ui.foundations.headline')"
                :intro="__('ui.foundations.lede')"
            />

            <p class="k-eyebrow">{{ __('ui.foundations.actions') }}</p>
            <div class="k-actions k-stack">
                <a class="k-btn k-btn-ink" href="{{ route('home') }}">{{ __('ui.foundations.primary') }}</a>
                <a class="k-btn k-btn-gold" href="{{ route('home') }}">{{ __('ui.foundations.gold') }}</a>
                <a class="k-btn k-btn-line" href="{{ route('home') }}">{{ __('ui.foundations.line') }}</a>
                <x-badge>{{ __('ui.placeholder') }}</x-badge>
            </div>

            <p class="k-eyebrow">{{ __('ui.foundations.messages') }}</p>
            <div class="k-prose k-stack-wide">
                <x-alert type="error">{{ __('ui.samples.error') }}</x-alert>
                <x-alert type="success">{{ __('ui.samples.success') }}</x-alert>
            </div>

            <p class="k-eyebrow">{{ __('ui.foundations.gallery') }}</p>
            <div class="k-stack">
                <x-gallery :names="['desert', 'forest', 'bridge']" :captions="__('ui.samples.gallery')" />
            </div>

            <p class="k-eyebrow">{{ __('ui.foundations.pages') }}</p>
            <ol class="k-note-list">
                @foreach ($entries as $entry)
                    <li>{{ $entry['title'] }}</li>
                @endforeach
            </ol>
            {{ $entries->links() }}

            <x-empty-state
                :title="__('ui.samples.empty_title')"
                :text="__('ui.samples.empty_text')"
            >
                <a class="k-btn k-btn-line" href="{{ route('home') }}">{{ __('ui.nav.home') }}</a>
            </x-empty-state>
        </div>
    </section>
@endsection
