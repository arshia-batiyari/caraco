@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section">
        <div class="k-wrap k-form-layout">
            <div>
                <x-section-heading :title="__('ui.consultation.title')" :intro="__('ui.consultation.lede')" />
                @include('partials.inquiry-form')
            </div>
        </div>
    </section>
@endsection
