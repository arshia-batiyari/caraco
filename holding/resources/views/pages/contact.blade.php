@extends('layouts.site')

@section('content')
    <x-breadcrumbs :items="[
        ['label' => __('ui.nav.home'), 'url' => route('home')],
        ['label' => __('ui.contact_page.headline')],
    ]" />

    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading level="1" :title="__('ui.contact_page.headline')" />

            <div class="k-form-layout">
                <form class="k-form" method="post" action="{{ route('contact.store') }}" novalidate>
                    @csrf
                    @if (session('sent'))
                        <x-alert type="success">{{ __('ui.contact_page.sent') }}</x-alert>
                    @endif
                    @if ($errors->any())
                        <x-alert type="error">{{ __('ui.validation.summary') }}</x-alert>
                    @endif
                    <x-form-field name="name" :label="__('ui.fields.name')" />
                    <x-form-field name="email" type="email" :label="__('ui.fields.email')" />
                    <x-form-field name="message" :label="__('ui.fields.message')" multiline />
                    <div class="k-hp" aria-hidden="true">
                        <label for="company_website">Website</label>
                        <input id="company_website" name="company_website" type="text" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <div class="k-actions">
                        <button class="k-btn k-btn-ink" type="submit">{{ __('ui.contact_page.submit') }}</button>
                    </div>
                </form>
                <div class="k-prose">
                    <p>{{ __('ui.contact_page.lede') }}</p>
                    @if ($email)
                        <p><a href="mailto:{{ $email }}">{{ $email }}</a></p>
                    @endif
                    @if ($phone)
                        <p><a href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a></p>
                    @endif
                    @if (filled($contact?->address))
                        <p>{{ $contact->address }}</p>
                    @endif
                    @if (filled($contact?->hours))
                        <p>{{ $contact->hours }}</p>
                    @endif
                    @if (filled($contact?->note))
                        <p>{{ $contact->note }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
@endsection
