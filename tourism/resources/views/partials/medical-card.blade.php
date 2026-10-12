@php
    $label = $record->publicTranslation();
    $url = $record->publicUrl($route);
    $image = $record instanceof \App\Models\MedicalProvider
        ? \App\Support\PreviewMedia::provider($record)
        : \App\Support\ImageStore::url($record->cover_path ?? null);
@endphp
@if ($label && $url)
    <article class="k-editorial-card">
        <a href="{{ $url }}">
            @if ($image)
                <img src="{{ $image }}" alt="{{ filled($label->cover_alt ?? null) ? $label->cover_alt : $label->name }}" loading="lazy" decoding="async">
            @endif
            <h3>{{ $label->name }}</h3>
            @if (filled($label->summary))<p>{{ $label->summary }}</p>@endif
        </a>
    </article>
@endif
