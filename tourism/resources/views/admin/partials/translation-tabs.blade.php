@php
    $prefix = $namePrefix ?? 'translations';
    $oldBase = trim(str_replace(['[', ']'], ['.', ''], $prefix), '.');
@endphp
<div data-locale-tabs>
    <div class="k-tabs" role="tablist">
        @foreach (config('karako.locales') as $code => $meta)
            <button type="button" role="tab" data-locale-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $meta['native'] }}</button>
        @endforeach
    </div>
    @foreach (config('karako.locales') as $code => $meta)
        @php $row = $rows->get($code); @endphp
        <div class="k-locale-panel" data-locale-panel="{{ $code }}" @if (! $loop->first) hidden @endif>
            @foreach ($fields as $field => [$label, $multiline])
                @php $key = $oldBase.'.'.$code.'.'.$field; @endphp
                <div class="k-field {{ $errors->has($key) ? 'is-invalid' : '' }}">
                    <label for="{{ $oldBase }}-{{ $field }}-{{ $code }}">{{ __($label) }}</label>
                    @if ($multiline)
                        <textarea id="{{ $oldBase }}-{{ $field }}-{{ $code }}" name="{{ $prefix }}[{{ $code }}][{{ $field }}]" rows="5">{{ old($key, $row?->{$field}) }}</textarea>
                    @else
                        <input id="{{ $oldBase }}-{{ $field }}-{{ $code }}" name="{{ $prefix }}[{{ $code }}][{{ $field }}]" value="{{ old($key, $row?->{$field}) }}">
                    @endif
                    @error($key)
                        <p class="k-field-error">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    @endforeach
</div>
