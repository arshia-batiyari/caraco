<form class="k-form" method="post" action="{{ route('consultation.store') }}">
    @csrf
    <p class="k-hp" aria-hidden="true">
        <label for="company-website">Website</label>
        <input id="company-website" name="company_website" tabindex="-1" autocomplete="off">
    </p>
    @if (session('sent'))
        <x-alert type="success">{{ __('ui.consultation.stored') }}</x-alert>
    @endif
    @if ($errors->any())
        <x-alert type="error">{{ __('ui.validation.summary') }}</x-alert>
    @endif
    <div class="k-field {{ $errors->has('name') ? 'is-invalid' : '' }}">
        <label for="name">{{ __('ui.fields.name') }}</label>
        <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name">
        @error('name')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-field {{ $errors->has('email') ? 'is-invalid' : '' }}">
        <label for="email">{{ __('ui.fields.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
        @error('email')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-field">
        <label for="phone">{{ __('ui.fields.phone') }}</label>
        <input id="phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
    </div>
    <div class="k-field">
        <label for="preferred-language">{{ __('ui.fields.language') }}</label>
        <select id="preferred-language" name="preferred_language">
            @foreach (config('karako.locales') as $code => $meta)
                <option value="{{ $code }}" @selected(old('preferred_language', app()->getLocale()) === $code)>{{ $meta['native'] }}</option>
            @endforeach
        </select>
    </div>
    <div class="k-field">
        <label for="tour-id">{{ __('ui.fields.tour') }}</label>
        <select id="tour-id" name="tour_id">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach ($tours as $tour)
                @php $label = $tour->publicTranslation(); @endphp
                @if ($label)
                    <option value="{{ $tour->id }}" @selected((string) old('tour_id', $selectedTour ?? '') === (string) $tour->id)>{{ $label->title ?? $label->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="k-field">
        <label for="destination-id">{{ __('ui.fields.destination') }}</label>
        <select id="destination-id" name="destination_id">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach ($destinations as $destination)
                @php $label = $destination->publicTranslation(); @endphp
                @if ($label)
                    <option value="{{ $destination->id }}" @selected((string) old('destination_id') === (string) $destination->id)>{{ $label->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="k-field">
        <label for="destination-text">{{ __('ui.fields.destination_text') }}</label>
        <input id="destination-text" name="destination_text" value="{{ old('destination_text') }}">
    </div>
    <div class="k-admin-grid">
        <div class="k-field">
            <label for="travel-start">{{ __('ui.fields.travel_start') }}</label>
            <input id="travel-start" name="travel_start" type="date" value="{{ old('travel_start') }}">
        </div>
        <div class="k-field">
            <label for="travel-end">{{ __('ui.fields.travel_end') }}</label>
            <input id="travel-end" name="travel_end" type="date" value="{{ old('travel_end') }}">
        </div>
    </div>
    <div class="k-field">
        <label for="travelers">{{ __('ui.fields.travelers') }}</label>
        <input id="travelers" name="travelers" type="number" min="1" max="99" value="{{ old('travelers') }}">
    </div>
    <div class="k-field {{ $errors->has('message') ? 'is-invalid' : '' }}">
        <label for="message">{{ __('ui.fields.message') }}</label>
        <textarea id="message" name="message" required>{{ old('message') }}</textarea>
        @error('message')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <label class="k-check {{ $errors->has('consent') ? 'is-invalid' : '' }}">
        <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
        <span>
            {{ __('ui.consultation.consent') }}
            @if ($privacy && $privacy->publicUrl('pages.show'))
                <a href="{{ $privacy->publicUrl('pages.show') }}">{{ $privacy->publicTranslation()->title }}</a>
            @endif
        </span>
    </label>
    @error('consent')<p class="k-field-error">{{ $message }}</p>@enderror
    <button class="k-btn k-btn-ink" type="submit">{{ __('ui.consultation.submit') }}</button>
    <p class="k-hint">{{ __('ui.consultation.not_reservation') }}</p>
</form>
