@extends('layouts.site')

@section('title', $copy['title'])

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => $copy['title']],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-badge>{{ __('ui.placeholder') }}</x-badge>
            <x-section-heading level="1" :eyebrow="$copy['eyebrow']" :title="$copy['headline']" />

            <div class="k-form-layout">
                <form class="k-form" method="post" action="{{ $action }}" novalidate>
                    @csrf
                    @if (session('sent'))
                        <x-alert type="success">{{ $copy['sent'] }}</x-alert>
                    @endif
                    @if ($errors->any())
                        <x-alert type="error">{{ __('ui.validation.summary') }}</x-alert>
                    @endif
                    <x-form-field name="name" :label="__('ui.fields.name')" />
                    <x-form-field name="email" type="email" :label="__('ui.fields.email')" />
                    <x-form-field name="message" :label="__('ui.fields.message')" multiline />
                    <div class="k-actions">
                        <button class="k-btn k-btn-ink" type="submit">{{ $copy['submit'] }}</button>
                    </div>
                </form>
                <div class="k-prose">
                    <p>{{ $copy['lede'] }}</p>
                </div>
            </div>
        </div>
    </section>
@endsection
