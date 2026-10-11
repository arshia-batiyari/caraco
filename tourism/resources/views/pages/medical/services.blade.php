@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading :title="__('ui.medical.services')" :intro="__('ui.medical.disclaimer')" />
            @if ($categories->isNotEmpty())
                <ul class="k-inline-links">
                    @foreach ($categories as $category)
                        @php $label = $category->publicTranslation(); $url = $category->publicUrl('medical.categories.show'); @endphp
                        @if ($label && $url)
                            <li><a href="{{ $url }}">{{ $label->name }}</a></li>
                        @endif
                    @endforeach
                </ul>
            @endif
            @if ($services->isEmpty())
                <x-empty-state :title="__('ui.medical.empty_title')" :text="__('ui.medical.empty_text')" />
            @else
                <div class="k-card-grid">
                    @foreach ($services as $service)
                        @include('partials.medical-card', ['record' => $service, 'route' => 'medical.services.show'])
                    @endforeach
                </div>
                {{ $services->links() }}
            @endif
        </div>
    </section>
@endsection
