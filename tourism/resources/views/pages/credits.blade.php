@extends('layouts.site')

@section('title', __('credits.title'))

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('credits.title')],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading level="1" :title="__('credits.headline')" :intro="__('credits.lede')" />
            <div class="k-credits">
                @foreach (config('sample-images') as $name => $image)
                    <article>
                        <img src="{{ asset('images/'.$image['file']) }}" alt="{{ __('credits.names.'.$name) }}">
                        <h3>{{ __('credits.names.'.$name) }}</h3>
                        <p>{{ $image['author'] }}</p>
                        <p><a href="{{ $image['license_url'] }}">{{ $image['license'] }}</a></p>
                        <p><a href="{{ $image['source'] }}">{{ __('credits.source') }}</a></p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
