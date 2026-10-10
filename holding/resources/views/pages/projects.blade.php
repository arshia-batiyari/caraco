@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => $heading],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading level="1" :title="$heading" />
            <div class="k-portfolio">
                @foreach ($projects as $project)
                    @php
                        $translation = $project->publicTranslation();
                        $image = \App\Support\ImageStore::url($project->image_path);
                    @endphp
                    <a class="k-portfolio-item {{ $loop->first ? 'is-lead' : '' }}" href="{{ $project->publicUrl('projects.show') }}">
                        @if ($image)
                            <img src="{{ $image }}" alt="{{ $translation->title }}" loading="lazy" decoding="async">
                        @endif
                        <span class="k-index-title">{{ $translation->title }}</span>
                        @if (filled($translation->summary))
                            <span class="k-index-text">{{ $translation->summary }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
            {{ $projects->links() }}
        </div>
    </section>
@endsection
