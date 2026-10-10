@props(['body' => null])

@php
    $paragraphs = \App\Support\LocaleText::paragraphs($body);
@endphp

@if ($paragraphs !== [])
    <div class="k-prose">
        @foreach ($paragraphs as $paragraph)
            <p>{!! nl2br(e($paragraph)) !!}</p>
        @endforeach
    </div>
@endif
