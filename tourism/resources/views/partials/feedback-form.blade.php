<form class="k-form" method="post" action="{{ route('feedback.store') }}">
    @csrf
    <p class="k-hp" aria-hidden="true">
        <label for="company-website">Website</label>
        <input id="company-website" name="company_website" tabindex="-1" autocomplete="off">
    </p>
    @if (session('sent'))
        <x-alert type="success">{{ session('sent') === true ? __('ui.feedback.stored') : session('sent') }}</x-alert>
    @endif
    @if ($errors->any())
        <x-alert type="error">{{ __('ui.validation.summary') }}</x-alert>
    @endif
    <div class="k-field {{ $errors->has('type') ? 'is-invalid' : '' }}">
        <label for="feedback-type">{{ __('ui.feedback.type') }}</label>
        <select id="feedback-type" name="type" required>
            @foreach (['general', 'complaint', 'suggestion', 'compliment'] as $type)
                <option value="{{ $type }}" @selected(old('type') === $type)>{{ __('ui.feedback.types.'.$type) }}</option>
            @endforeach
        </select>
        @error('type')<p class="k-field-error">{{ $message }}</p>@enderror
        <p class="k-hint">{{ __('ui.feedback.complaint_private') }}</p>
    </div>
    <label class="k-check">
        <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous'))>
        <span>{{ __('ui.feedback.anonymous_choice') }}</span>
    </label>
    <p class="k-hint">{{ __('ui.feedback.anonymous_hint') }}</p>
    <div class="k-field {{ $errors->has('name') ? 'is-invalid' : '' }}">
        <label for="feedback-name">{{ __('ui.fields.name') }}</label>
        <input id="feedback-name" name="name" value="{{ old('name') }}" autocomplete="name">
        @error('name')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-follow">
    <input id="follow-up" class="k-reveal" type="checkbox" name="wants_follow_up" value="1" @checked(old('wants_follow_up'))>
    <label class="k-check" for="follow-up">{{ __('ui.feedback.follow_up') }}</label>
    <div class="k-reveal-panel">
        <div class="k-field {{ $errors->has('email') ? 'is-invalid' : '' }}">
            <label for="feedback-email">{{ __('ui.fields.email') }}</label>
            <input id="feedback-email" name="email" type="email" value="{{ old('email') }}" autocomplete="email">
            @error('email')<p class="k-field-error">{{ $message }}</p>@enderror
        </div>
        <div class="k-field">
            <label for="feedback-phone">{{ __('ui.fields.phone') }}</label>
            <input id="feedback-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel">
        </div>
        <p class="k-hint">{{ __('ui.feedback.contact_hint') }}</p>
    </div>
    </div>
    <div class="k-field">
        <label for="feedback-tour">{{ __('ui.fields.tour') }}</label>
        <select id="feedback-tour" name="tour_id">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach ($tours as $tour)
                @if ($tour->publicTranslation())
                    <option value="{{ $tour->id }}" @selected((string) old('tour_id') === (string) $tour->id)>{{ $tour->publicTranslation()->title }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="k-field">
        <label for="feedback-service">{{ __('ui.feedback.service') }}</label>
        <select id="feedback-service" name="service_id">
            <option value="">{{ __('ui.fields.none') }}</option>
            @foreach ($services as $service)
                @if ($service->publicTranslation())
                    <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->publicTranslation()->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
    @foreach ([
        'rating_overall' => 'ui.feedback.rating_overall',
        'rating_accommodation' => 'ui.feedback.rating_accommodation',
        'rating_transport' => 'ui.feedback.rating_transport',
        'rating_organization' => 'ui.feedback.rating_organization',
        'rating_service' => 'ui.feedback.rating_service',
    ] as $name => $label)
        <fieldset class="k-choice-set">
            <legend>{{ __($label) }}</legend>
            <div class="k-choice-row">
                @for ($score = 1; $score <= 5; $score++)
                    <label class="k-choice">
                        <input type="radio" name="{{ $name }}" value="{{ $score }}" @checked((string) old($name) === (string) $score)>
                        <span>{{ $score }} ★</span>
                    </label>
                @endfor
            </div>
        </fieldset>
    @endforeach
    <div class="k-field {{ $errors->has('title') ? 'is-invalid' : '' }}">
        <label for="feedback-title">{{ __('ui.feedback.subject') }}</label>
        <input id="feedback-title" name="title" value="{{ old('title') }}" required>
        @error('title')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-field {{ $errors->has('message') ? 'is-invalid' : '' }}">
        <label for="feedback-message">{{ __('ui.fields.message') }}</label>
        <textarea id="feedback-message" name="message" required>{{ old('message') }}</textarea>
        @error('message')<p class="k-field-error">{{ $message }}</p>@enderror
    </div>
    <div class="k-field">
        <label for="feedback-language">{{ __('ui.fields.language') }}</label>
        <select id="feedback-language" name="preferred_language">
            @foreach (config('karako.locales') as $code => $meta)
                <option value="{{ $code }}" @selected(old('preferred_language', app()->getLocale()) === $code)>{{ $meta['native'] }}</option>
            @endforeach
        </select>
    </div>
    <label class="k-check {{ $errors->has('consent') ? 'is-invalid' : '' }}">
        <input type="checkbox" name="consent" value="1" @checked(old('consent')) required>
        <span>
            {{ __('ui.feedback.consent') }}
            @if ($privacy && $privacy->publicUrl('pages.show'))
                <a href="{{ $privacy->publicUrl('pages.show') }}">{{ $privacy->publicTranslation()->title }}</a>
            @endif
        </span>
    </label>
    @error('consent')<p class="k-field-error">{{ $message }}</p>@enderror
    <button class="k-btn k-btn-ink" type="submit">{{ __('ui.feedback.submit') }}</button>
    <p class="k-hint">{{ __('ui.feedback.not_published') }}</p>
</form>
