@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section">
        <div class="k-wrap">
            <x-section-heading :title="__('ui.attractions.title')" :intro="__('ui.attractions.description')" />
            @if ($errors->any())
                <x-alert type="error">{{ $errors->first() }}</x-alert>
            @endif
            <form class="k-admin-filters" method="get" action="{{ route('attractions.index') }}">
                <div class="k-field">
                    <label for="attraction-q">{{ __('ui.attractions.search') }}</label>
                    <input id="attraction-q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="80">
                </div>
                <div class="k-field">
                    <label for="attraction-country">{{ __('ui.attractions.country') }}</label>
                    <select id="attraction-country" name="country">
                        <option value="">{{ __('ui.fields.none') }}</option>
                        @foreach ($countries as $country)
                            @if ($country->publicTranslation())
                                <option value="{{ $country->id }}" @selected((string) ($filters['country'] ?? '') === (string) $country->id)>{{ $country->publicTranslation()->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="attraction-city">{{ __('ui.attractions.city') }}</label>
                    <select id="attraction-city" name="city">
                        <option value="">{{ __('ui.fields.none') }}</option>
                        @foreach ($cities as $city)
                            @if ($city->publicTranslation())
                                <option value="{{ $city->id }}" @selected((string) ($filters['city'] ?? '') === (string) $city->id)>{{ $city->publicTranslation()->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="k-field">
                    <label for="attraction-category">{{ __('ui.attractions.category') }}</label>
                    <select id="attraction-category" name="category">
                        <option value="">{{ __('ui.fields.none') }}</option>
                        @foreach ($categories as $category)
                            @if ($category->publicTranslation())
                                <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->publicTranslation()->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <button class="k-btn k-btn-ink" type="submit">{{ __('ui.attractions.apply') }}</button>
            </form>
            @if ($attractions->isEmpty())
                <x-empty-state :title="__('ui.attractions.empty_title')" :text="__('ui.attractions.empty_text')" />
            @else
                <div class="k-card-grid">
                    @foreach ($attractions as $attraction)
                        @include('partials.attraction-card', ['record' => $attraction])
                    @endforeach
                </div>
                {{ $attractions->links() }}
            @endif
        </div>
    </section>
@endsection
