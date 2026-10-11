@extends('layouts.admin')

@section('title', __('admin.contact'))

@section('content')
    <h1>{{ __('admin.contact') }}</h1>
    <form class="k-form" method="post" action="{{ route('admin.contact.update') }}">
        @csrf
        @method('PUT')
        <div class="k-field">
            <label for="contact_email">{{ __('ui.fields.email') }}</label>
            <input id="contact_email" name="contact_email" type="email" value="{{ old('contact_email', $email) }}">
        </div>
        <div class="k-field">
            <label for="contact_phone">{{ __('admin.phone') }}</label>
            <input id="contact_phone" name="contact_phone" value="{{ old('contact_phone', $phone) }}">
        </div>
        <div data-locale-tabs>
            <div class="k-tabs" role="tablist">
                @foreach (config('karako.locales') as $code => $meta)
                    <button type="button" role="tab" data-locale-tab="{{ $code }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $meta['native'] }}</button>
                @endforeach
            </div>
            @foreach (config('karako.locales') as $code => $meta)
                @php $row = $translations->get($code); @endphp
                <div class="k-locale-panel" data-locale-panel="{{ $code }}" @if (! $loop->first) hidden @endif>
                    <div class="k-field">
                        <label for="address-{{ $code }}">{{ __('admin.address') }}</label>
                        <textarea id="address-{{ $code }}" name="translations[{{ $code }}][address]" rows="3">{{ old('translations.'.$code.'.address', $row?->address) }}</textarea>
                    </div>
                    <div class="k-field">
                        <label for="hours-{{ $code }}">{{ __('admin.hours') }}</label>
                        <input id="hours-{{ $code }}" name="translations[{{ $code }}][hours]" value="{{ old('translations.'.$code.'.hours', $row?->hours) }}">
                    </div>
                    <div class="k-field">
                        <label for="note-{{ $code }}">{{ __('admin.note') }}</label>
                        <textarea id="note-{{ $code }}" name="translations[{{ $code }}][note]" rows="3">{{ old('translations.'.$code.'.note', $row?->note) }}</textarea>
                    </div>
                </div>
            @endforeach
        </div>

        <h2>{{ __('admin.social') }}</h2>
        @foreach ($socialLinks as $link)
            <fieldset class="k-admin-set">
                <div class="k-admin-grid">
                    <div class="k-field">
                        <label for="platform-{{ $link->id }}">{{ __('admin.platform') }}</label>
                        <select id="platform-{{ $link->id }}" name="social[{{ $link->id }}][platform]">
                            @foreach (['instagram', 'linkedin', 'telegram', 'youtube', 'x', 'website'] as $platform)
                                <option value="{{ $platform }}" @selected($link->platform === $platform)>{{ __('ui.social.'.$platform) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="k-field">
                        <label for="social-url-{{ $link->id }}">{{ __('admin.url') }}</label>
                        <input id="social-url-{{ $link->id }}" name="social[{{ $link->id }}][url]" value="{{ old('social.'.$link->id.'.url', $link->url) }}">
                    </div>
                    <div class="k-field">
                        <label for="social-position-{{ $link->id }}">{{ __('admin.position') }}</label>
                        <input id="social-position-{{ $link->id }}" name="social[{{ $link->id }}][position]" type="number" value="{{ $link->position }}">
                    </div>
                </div>
                <input type="hidden" name="social[{{ $link->id }}][is_published]" value="0">
                <label class="k-check">
                    <input type="checkbox" name="social[{{ $link->id }}][is_published]" value="1" @checked($link->is_published)>
                    <span>{{ __('admin.published') }}</span>
                </label>
                <label class="k-check">
                    <input type="checkbox" name="remove_social[]" value="{{ $link->id }}">
                    <span>{{ __('admin.delete') }}</span>
                </label>
            </fieldset>
        @endforeach
        <fieldset class="k-admin-set">
            <legend>{{ __('admin.add_link') }}</legend>
            <div class="k-admin-grid">
                <div class="k-field">
                    <label for="new-platform">{{ __('admin.platform') }}</label>
                    <select id="new-platform" name="new_social[platform]">
                        <option value="">—</option>
                        @foreach (['instagram', 'linkedin', 'telegram', 'youtube', 'x', 'website'] as $platform)
                            <option value="{{ $platform }}">{{ __('ui.social.'.$platform) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="new-social-url">{{ __('admin.url') }}</label>
                    <input id="new-social-url" name="new_social[url]" value="{{ old('new_social.url') }}">
                </div>
                <div class="k-field">
                    <label for="new-social-position">{{ __('admin.position') }}</label>
                    <input id="new-social-position" name="new_social[position]" type="number" value="0">
                </div>
            </div>
        </fieldset>
        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
@endsection
