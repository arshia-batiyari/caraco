@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading :title="$translation->name" :intro="$translation->summary" />
            @if ($tours->isEmpty())
                <x-empty-state :title="__('ui.tours.empty_title')" :text="__('ui.tours.empty_text')" />
            @else
                <div class="k-card-grid">
                    @foreach ($tours as $tour)
                        @include('partials.tour-card', ['tour' => $tour])
                    @endforeach
                </div>
                {{ $tours->links() }}
            @endif
        </div>
    </section>
@endsection
