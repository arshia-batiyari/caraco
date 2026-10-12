@extends('layouts.site')

@section('content')
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    <x-breadcrumbs :items="$crumbs" />
    <section class="k-section k-catalog">
        <div class="k-wrap">
            <x-section-heading :title="__('ui.tours.index_title')" :intro="__('ui.tours.index_description')" />
            <div class="k-listing">
                <details class="k-filters" @if ($filtered) open @endif>
                    <summary>{{ __('ui.filters.title') }}</summary>
                    <form method="get" action="{{ route('tours.index') }}">
                        @if ($errors->has('filters'))
                            <x-alert type="error">{{ $errors->first('filters') }}</x-alert>
                        @endif
                        <div class="k-field">
                            <label for="filter-scope">{{ __('ui.filters.scope') }}</label>
                            <select id="filter-scope" name="scope">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                <option value="domestic" @selected(($filters['scope'] ?? '') === 'domestic')>{{ __('ui.tours.domestic') }}</option>
                                <option value="international" @selected(($filters['scope'] ?? '') === 'international')>{{ __('ui.tours.international') }}</option>
                            </select>
                        </div>
                        <div class="k-field">
                            <label for="filter-destination">{{ __('ui.filters.destination') }}</label>
                            <select id="filter-destination" name="destination">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @foreach ($options['destinations'] as $destination)
                                    @if ($destination->publicTranslation())
                                        <option value="{{ $destination->id }}" @selected((string) ($filters['destination'] ?? '') === (string) $destination->id)>{{ $destination->publicTranslation()->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="k-field">
                            <label for="filter-city">{{ __('ui.filters.city') }}</label>
                            <select id="filter-city" name="city">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @foreach ($options['cities'] as $city)
                                    @if ($city->publicTranslation())
                                        <option value="{{ $city->id }}" @selected((string) ($filters['city'] ?? '') === (string) $city->id)>{{ $city->publicTranslation()->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="k-field">
                            <label for="filter-category">{{ __('ui.filters.category') }}</label>
                            <select id="filter-category" name="category">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @foreach ($options['categories'] as $category)
                                    @if ($category->publicTranslation())
                                        <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->publicTranslation()->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="k-field">
                            <label for="filter-transport">{{ __('ui.filters.transport') }}</label>
                            <select id="filter-transport" name="transport">
                                <option value="">{{ __('ui.fields.none') }}</option>
                                @foreach ($options['transports'] as $transport)
                                    @if ($transport->publicTranslation())
                                        <option value="{{ $transport->id }}" @selected((string) ($filters['transport'] ?? '') === (string) $transport->id)>{{ $transport->publicTranslation()->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="k-admin-grid">
                            <div class="k-field">
                                <label for="depart-from">{{ __('ui.filters.depart_from') }}</label>
                                <input id="depart-from" name="depart_from" type="date" value="{{ $filters['depart_from'] ?? '' }}">
                            </div>
                            <div class="k-field">
                                <label for="depart-to">{{ __('ui.filters.depart_to') }}</label>
                                <input id="depart-to" name="depart_to" type="date" value="{{ $filters['depart_to'] ?? '' }}">
                            </div>
                        </div>
                        <div class="k-admin-grid">
                            <div class="k-field">
                                <label for="days-min">{{ __('ui.filters.days_min') }}</label>
                                <input id="days-min" name="days_min" type="number" min="1" value="{{ $filters['days_min'] ?? '' }}">
                            </div>
                            <div class="k-field">
                                <label for="days-max">{{ __('ui.filters.days_max') }}</label>
                                <input id="days-max" name="days_max" type="number" min="1" value="{{ $filters['days_max'] ?? '' }}">
                            </div>
                        </div>
                        <div class="k-field">
                            <label for="currency">{{ __('ui.filters.currency') }}</label>
                            <input id="currency" name="currency" value="{{ $filters['currency'] ?? '' }}" maxlength="3" autocapitalize="characters">
                        </div>
                        <div class="k-admin-grid">
                            <div class="k-field">
                                <label for="price-min">{{ __('ui.filters.price_min') }}</label>
                                <input id="price-min" name="price_min" inputmode="decimal" value="{{ $filters['price_min'] ?? '' }}">
                            </div>
                            <div class="k-field">
                                <label for="price-max">{{ __('ui.filters.price_max') }}</label>
                                <input id="price-max" name="price_max" inputmode="decimal" value="{{ $filters['price_max'] ?? '' }}">
                            </div>
                        </div>
                        <label class="k-check">
                            <input type="checkbox" name="discounted" value="1" @checked(($filters['discounted'] ?? '') === '1')>
                            <span>{{ __('ui.filters.discounted') }}</span>
                        </label>
                        <label class="k-check">
                            <input type="checkbox" name="available" value="1" @checked(($filters['available'] ?? '') === '1')>
                            <span>{{ __('ui.filters.available') }}</span>
                        </label>
                        <div class="k-actions">
                            <button class="k-btn k-btn-ink" type="submit">{{ __('ui.filters.apply') }}</button>
                            <a class="k-btn k-btn-line" href="{{ route('tours.index') }}">{{ __('ui.filters.reset') }}</a>
                        </div>
                    </form>
                </details>
                @if ($tours->isEmpty())
                    <x-empty-state :title="__('ui.tours.empty_title')" :text="__('ui.tours.empty_text')" />
                @else
                    <div class="k-card-grid">
                        @foreach ($tours as $tour)
                            @include('partials.tour-card', ['tour' => $tour])
                        @endforeach
                    </div>
                    {{ $tours->links() }}
                @endif
            </div>
        </div>
    </section>
@endsection
