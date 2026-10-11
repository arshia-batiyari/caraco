@extends('layouts.admin')

@php
    $departures = ($record->exists ? $record->departures : collect())->values();
    $days = ($record->exists ? $record->days : collect())->values();
    $points = ($record->exists ? $record->routePoints : collect())->values();
    $stays = ($record->exists ? $record->stays : collect())->values();
    $faqs = ($record->exists ? $record->faqs : collect())->values();
    $selectedTransports = old('transport_ids', $record->exists ? $record->transports->pluck('id')->all() : []);
    $selectedServices = old('service_ids', $record->exists ? $record->services->pluck('id')->all() : []);
@endphp

@section('content')
    <p><a href="{{ route('admin.tours.index') }}">{{ __('admin.tours') }}</a></p>
    <h1>{{ $record->exists ? ($record->translations->first()?->title ?? __('admin.edit')) : __('admin.create') }}</h1>
    <form class="k-form" method="post" action="{{ $record->exists ? route('admin.tours.update', $record) : route('admin.tours.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($record->exists) @method('PUT') @endif
        <div class="k-admin-grid">
            <div class="k-field">
                <label for="scope">{{ __('admin.scope') }}</label>
                <select id="scope" name="scope">
                    <option value="domestic" @selected(old('scope', $record->scope) === 'domestic')>{{ __('ui.tours.domestic') }}</option>
                    <option value="international" @selected(old('scope', $record->scope) === 'international')>{{ __('ui.tours.international') }}</option>
                </select>
            </div>
            <div class="k-field">
                <label for="position">{{ __('admin.position') }}</label>
                <input id="position" name="position" type="number" min="0" value="{{ old('position', $record->position) }}">
            </div>
        </div>
        <div class="k-field">
            <label for="destination-id">{{ __('admin.destination') }}</label>
            <select id="destination-id" name="destination_id">
                <option value="">{{ __('ui.fields.none') }}</option>
                @foreach ($destinations as $destination)
                    <option value="{{ $destination->id }}" @selected((string) old('destination_id', $record->destination_id) === (string) $destination->id)>{{ $destination->translations->first()?->name ?? '#'.$destination->id }}</option>
                @endforeach
            </select>
        </div>
        <div class="k-admin-grid">
            <div class="k-field">
                <label for="category-id">{{ __('admin.category') }}</label>
                <select id="category-id" name="category_id">
                    <option value="">{{ __('ui.fields.none') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $record->category_id) === (string) $category->id)>{{ $category->translations->first()?->name ?? '#'.$category->id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="k-field">
                <label for="style-id">{{ __('admin.style') }}</label>
                <select id="style-id" name="travel_style_id">
                    <option value="">{{ __('ui.fields.none') }}</option>
                    @foreach ($styles as $style)
                        <option value="{{ $style->id }}" @selected((string) old('travel_style_id', $record->travel_style_id) === (string) $style->id)>{{ $style->translations->first()?->name ?? '#'.$style->id }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="k-check"><input type="hidden" name="is_published" value="0"><input type="checkbox" name="is_published" value="1" @checked(filter_var(old('is_published', $record->is_published), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
        <label class="k-check"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(filter_var(old('is_featured', $record->is_featured), FILTER_VALIDATE_BOOL))><span>{{ __('admin.featured') }}</span></label>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.pricing') }}</legend>
            <div class="k-admin-grid">
                <div class="k-field"><label for="currency">{{ __('admin.currency') }}</label><input id="currency" name="currency" maxlength="3" value="{{ old('currency', $record->currency) }}"></div>
                <div class="k-field"><label for="base-price">{{ __('admin.base_price') }}</label><input id="base-price" name="base_price" value="{{ old('base_price', $record->base_price) }}"></div>
                <div class="k-field"><label for="discount-price">{{ __('admin.discount_price') }}</label><input id="discount-price" name="discount_price" value="{{ old('discount_price', $record->discount_price) }}"></div>
                <div class="k-field"><label for="discount-percent">{{ __('admin.discount_percent') }}</label><input id="discount-percent" name="discount_percent" value="{{ old('discount_percent', $record->discount_percent) }}"></div>
                <div class="k-field"><label for="discount-start">{{ __('admin.discount_starts') }}</label><input id="discount-start" name="discount_starts_at" type="date" value="{{ old('discount_starts_at', $record->discount_starts_at?->toDateString()) }}"></div>
                <div class="k-field"><label for="discount-end">{{ __('admin.discount_ends') }}</label><input id="discount-end" name="discount_ends_at" type="date" value="{{ old('discount_ends_at', $record->discount_ends_at?->toDateString()) }}"></div>
            </div>
            <p class="k-hint">{{ __('admin.discount_hint') }}</p>
        </fieldset>

        <div class="k-admin-grid">
            <div class="k-field"><label for="days">{{ __('admin.days') }}</label><input id="days" name="duration_days" type="number" min="1" value="{{ old('duration_days', $record->duration_days) }}"></div>
            <div class="k-field"><label for="nights">{{ __('admin.nights') }}</label><input id="nights" name="duration_nights" type="number" min="0" value="{{ old('duration_nights', $record->duration_nights) }}"></div>
        </div>

        @include('admin.partials.translation-tabs', ['rows' => $record->translations->keyBy('locale'), 'fields' => [
            'title' => ['admin.title', false],
            'slug' => ['admin.slug', false],
            'summary' => ['admin.summary', true],
            'description' => ['admin.description', true],
            'departure_location' => ['admin.departure_location', false],
            'meeting_instructions' => ['admin.meeting', true],
            'transport_details' => ['admin.transport_details', true],
            'meals' => ['admin.meals', true],
            'notes' => ['admin.notes', true],
            'requirements' => ['admin.requirements', true],
            'restrictions' => ['admin.restrictions', true],
            'terms' => ['admin.terms', true],
            'cancellation' => ['admin.cancellation', true],
            'meta_title' => ['admin.meta_title', false],
            'meta_description' => ['admin.meta_description', true],
            'canonical_url' => ['admin.canonical', false],
        ]])

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.transports') }}</legend>
            @foreach ($transports as $transport)
                <label class="k-check">
                    <input type="checkbox" name="transport_ids[]" value="{{ $transport->id }}" @checked(in_array($transport->id, array_map('intval', (array) $selectedTransports), true))>
                    <span>{{ $transport->translations->first()?->name ?? $transport->mode }}</span>
                </label>
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.services') }}</legend>
            @foreach ($services as $service)
                <label class="k-check">
                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, array_map('intval', (array) $selectedServices), true))>
                    <span>{{ $service->translations->first()?->name ?? $service->type }}</span>
                </label>
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.departures') }}</legend>
            @foreach ($departures->concat([new \App\Models\TourDeparture, new \App\Models\TourDeparture]) as $index => $departure)
                <div class="k-admin-grid">
                    <div class="k-field"><label>{{ __('admin.departure_date') }}</label><input name="departures[{{ $index }}][departure_date]" type="date" value="{{ old('departures.'.$index.'.departure_date', $departure->departure_date?->toDateString()) }}"></div>
                    <div class="k-field"><label>{{ __('admin.return_date') }}</label><input name="departures[{{ $index }}][return_date]" type="date" value="{{ old('departures.'.$index.'.return_date', $departure->return_date?->toDateString()) }}"></div>
                </div>
                <label class="k-check"><input type="hidden" name="departures[{{ $index }}][is_published]" value="0"><input type="checkbox" name="departures[{{ $index }}][is_published]" value="1" @checked(filter_var(old('departures.'.$index.'.is_published', $departure->exists ? $departure->is_published : true), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.stays') }}</legend>
            @foreach ($stays->concat([new \App\Models\TourHotel]) as $index => $stay)
                <div class="k-field">
                    <label>{{ __('admin.hotel') }}</label>
                    <select name="stays[{{ $index }}][hotel_id]">
                        <option value="">{{ __('ui.fields.none') }}</option>
                        @foreach ($hotels as $hotel)
                            <option value="{{ $hotel->id }}" @selected((string) old('stays.'.$index.'.hotel_id', $stay->hotel_id) === (string) $hotel->id)>{{ $hotel->translations->first()?->name ?? '#'.$hotel->id }}</option>
                        @endforeach
                    </select>
                </div>
                @include('admin.partials.translation-tabs', [
                    'namePrefix' => 'stays['.$index.'][translations]',
                    'rows' => $stay->exists ? $stay->translations->keyBy('locale') : collect(),
                    'fields' => ['arrangement' => ['admin.arrangement', true]],
                ])
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.itinerary') }}</legend>
            @foreach ($days->concat([new \App\Models\ItineraryDay, new \App\Models\ItineraryDay]) as $index => $day)
                <div class="k-field"><label>{{ __('admin.day_number') }}</label><input name="itinerary[{{ $index }}][day_number]" type="number" min="1" value="{{ old('itinerary.'.$index.'.day_number', $day->day_number) }}"></div>
                @include('admin.partials.translation-tabs', [
                    'namePrefix' => 'itinerary['.$index.'][translations]',
                    'rows' => $day->exists ? $day->translations->keyBy('locale') : collect(),
                    'fields' => ['title' => ['admin.title', false], 'body' => ['admin.body', true]],
                ])
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.route') }}</legend>
            @foreach ($points->concat([new \App\Models\RoutePoint, new \App\Models\RoutePoint]) as $index => $point)
                <div class="k-admin-grid">
                    <div class="k-field"><label>{{ __('admin.latitude') }}</label><input name="points[{{ $index }}][latitude]" value="{{ old('points.'.$index.'.latitude', $point->latitude) }}"></div>
                    <div class="k-field"><label>{{ __('admin.longitude') }}</label><input name="points[{{ $index }}][longitude]" value="{{ old('points.'.$index.'.longitude', $point->longitude) }}"></div>
                    <div class="k-field"><label>{{ __('admin.day_number') }}</label><input name="points[{{ $index }}][day_number]" type="number" min="1" value="{{ old('points.'.$index.'.day_number', $point->day?->day_number) }}"></div>
                </div>
                @include('admin.partials.translation-tabs', [
                    'namePrefix' => 'points['.$index.'][translations]',
                    'rows' => $point->exists ? $point->translations->keyBy('locale') : collect(),
                    'fields' => ['name' => ['admin.name', false], 'description' => ['admin.description', true]],
                ])
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.faqs') }}</legend>
            @foreach ($faqs->concat([new \App\Models\Faq]) as $index => $faq)
                <label class="k-check"><input type="hidden" name="faqs[{{ $index }}][is_published]" value="0"><input type="checkbox" name="faqs[{{ $index }}][is_published]" value="1" @checked(filter_var(old('faqs.'.$index.'.is_published', $faq->exists ? $faq->is_published : true), FILTER_VALIDATE_BOOL))><span>{{ __('admin.published') }}</span></label>
        <p class="k-field-hint">{{ __('admin.published_hint') }}</p>
                @include('admin.partials.translation-tabs', [
                    'namePrefix' => 'faqs['.$index.'][translations]',
                    'rows' => $faq->exists ? $faq->translations->keyBy('locale') : collect(),
                    'fields' => ['question' => ['admin.question', false], 'answer' => ['admin.answer', true]],
                ])
            @endforeach
        </fieldset>

        <fieldset class="k-admin-set">
            <legend>{{ __('admin.images') }}</legend>
            <input name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
            @if ($record->exists)
                @foreach ($record->images as $image)
                    <label class="k-check">
                        <img class="k-admin-preview" src="{{ \App\Support\ImageStore::url($image->path) }}" alt="">
                        <input type="radio" name="cover_image" value="{{ $image->id }}" @checked(old('cover_image', $image->is_cover ? $image->id : null) == $image->id)>
                        <span>{{ __('admin.cover') }}</span>
                        <input type="checkbox" name="remove_images[]" value="{{ $image->id }}">
                        <span>{{ __('admin.remove') }}</span>
                    </label>
                @endforeach
            @endif
        </fieldset>

        <button class="k-btn k-btn-ink" type="submit">{{ __('admin.save') }}</button>
    </form>
    @if ($record->exists)
        <form class="k-admin-delete" method="post" action="{{ route('admin.tours.destroy', $record) }}">@csrf @method('DELETE')
            <label class="k-check"><input type="checkbox" name="confirm_delete" value="1"><span>{{ __('admin.confirm_delete') }}</span></label><button class="k-btn k-btn-line" type="submit">{{ __('admin.delete') }}</button></form>
    @endif
@endsection
