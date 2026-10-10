@props(['names' => [], 'captions' => []])

<div {{ $attributes->class(['k-gallery']) }}>
    @foreach ($names as $name)
        <x-photo
            :name="$name"
            :alt="$captions[$name]['alt'] ?? ''"
            :caption="$captions[$name]['caption'] ?? null"
        />
    @endforeach
</div>
