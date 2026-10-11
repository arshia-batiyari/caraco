<form class="k-form" method="post" action="{{ route('medical.store') }}">
    @csrf
    <p class="k-hp" aria-hidden="true">
        <label for="medical-company">Website</label>
        <input id="medical-company" name="company_website" tabindex="-1" autocomplete="off">
    </p>
    @if (session('sent'))
        <x-alert type="success">{{ session('sent') === true ? __('ui.medical.stored') : session('sent') }}</x-alert>
    @endif
    @if ($errors->any())
        <x-alert type="error">{{ __('ui.validation.summary') }}</x-alert>
    @endif
    <div class="k-field {{ $errors->has('name') ? 'is-invalid' : '' }}">
        <label for="medical-name">{{ __('ui.fields.name') }}</label>
        <input id="medical-name" name="name" value="{{ old('name') }}" required autocomplete="name">
        @error('name')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-field {{ $errors->has('email') ? 'is-invalid' : '' }}">
        <label for="medical-email">{{ __('ui.fields.email') }}</label>
        <input id="medical-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        @error('email')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-field">
        <label for="medical-phone">{{ __('ui.fields.phone') }}</label>
        <input id="medical-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
    </div>
    <div class="k-field">
        <label for="medical-language">{{ __('ui.fields.language') }}</label>
        <select id="medical-language" name="preferred_language">
            @foreach (config('karako.locales') as $code => $meta)
                <option value="{{ $code }}" @selected(old('preferred_language', app()->getLocale()) === $code)>{{ $meta['native'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="k-field">
        <label for="medical-country">{{ __('ui.medical.country') }}</label>
        <input id="medical-country" name="country" value="{{ old('country') }}" maxlength="80" autocomplete="country-name">
        <p class="k-field-hint">{{ __('ui.medical.country_hint') }}</p>
    </div>
    <div class="k-field">
        <label for="medical-service">{{ __('ui.medical.service') }}</label>
        <select id="medical-service" name="medical_service_id">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach ($services as $service)
                @php $label = $service->publicTranslation(); @endphp
                @if ($label)
                    <option value="{{ $service->id }}" @selected((string) old('medical_service_id', $selected ?? '') === (string) $service->id)>{{ $label->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="k-field {{ $errors->has('message') ? 'is-invalid' : '' }}">
        <label for="medical-message">{{ __('ui.medical.questions') }}</label>
        <textarea id="medical-message" name="message" rows="5" required>{{ old('message') }}</textarea>
        <p class="k-field-hint">{{ __('ui.medical.questions_hint') }}</p>
        @error('message')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <label class="k-check {{ $errors->has('consent') ? 'is-invalid' : '' }}">
        <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
        <span>{{ __('ui.consultation.consent') }}</span>
    </label>
    @error('consent')<p class="k-field-error">{{ $message }}</p>@enderror
    @if ($privacy && $privacy->publicUrl('pages.show'))
        <p><a href="{{ $privacy->publicUrl('pages.show') }}">{{ $privacy->publicTranslation()->title }}</a></p>
    @endif
    <button class="k-btn k-btn-ink" type="submit">{{ __('ui.medical.submit') }}</button>
</form>
