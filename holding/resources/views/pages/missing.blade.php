@extends('layouts.site')

@section('title', __('ui.missing.title'))

@section('content')
    <section class="k-section">
        <div class="k-wrap">
            <x-empty-state
                level="1"
                :title="__('ui.missing.title')"
                :text="__('ui.missing.text')"
            >
                <a class="k-btn k-btn-ink" href="{{ route('home') }}">{{ __('ui.nav.home') }}</a>
            </x-empty-state>
        </div>
    </section>
@endsection
