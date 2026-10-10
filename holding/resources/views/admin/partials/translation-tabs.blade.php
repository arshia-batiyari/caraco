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
                <div class="k-field {{ $errors->has('translations.'.$code.'.'.$field) ? 'is-invalid' : '' }}">
                    <label for="{{ $field }}-{{ $code }}">{{ __($label) }}</label>
                    @if ($multiline)
                        <textarea id="{{ $field }}-{{ $code }}" name="translations[{{ $code }}][{{ $field }}]" rows="6">{{ old('translations.'.$code.'.'.$field, $row?->{$field}) }}</textarea>
                    @else
                        <input id="{{ $field }}-{{ $code }}" name="translations[{{ $code }}][{{ $field }}]" value="{{ old('translations.'.$code.'.'.$field, $row?->{$field}) }}">
                    @endif
                    @if ($errors->has('translations.'.$code.'.'.$field))
                        <p class="k-field-error">{{ $errors->first('translations.'.$code.'.'.$field) }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endforeach
</div>
