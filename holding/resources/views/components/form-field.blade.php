@props(['name', 'label', 'type' => 'text', 'multiline' => false])

@php
    $error = $errors->first($name);
@endphp

<div class="k-field {{ $error ? 'is-invalid' : '' }}">
    <label for="{{ $name }}">{{ $label }}</label>
    @if ($multiline)
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            rows="6"
            @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        >{{ old($name) }}</textarea>
    @else
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name) }}"
            @if ($type === 'email') autocomplete="email" inputmode="email" @endif
            @if ($name === 'name') autocomplete="name" @endif
            @if ($error) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
        >
    @endif
    @if ($error)
        <p class="k-field-error" id="{{ $name }}-error">{{ $error }}</p>
    @endif
</div>
