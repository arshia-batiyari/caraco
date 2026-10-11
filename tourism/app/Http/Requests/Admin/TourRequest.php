<?php

namespace App\Http\Requests\Admin;

use App\Support\TourPrice;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;

class TourRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->dropEmptyUploads('images');

        $merge = [];
        foreach ([
            'destination_id',
            'category_id',
            'travel_style_id',
            'base_price',
            'discount_price',
            'discount_percent',
            'discount_starts_at',
            'discount_ends_at',
            'duration_days',
            'duration_nights',
            'currency',
        ] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }
        if (filled($this->input('currency'))) {
            $merge['currency'] = strtoupper(trim((string) $this->input('currency')));
        }
        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $text = ['nullable', 'string', 'max:5000'];

        return [
            'scope' => ['required', 'in:domestic,international'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'travel_style_id' => ['nullable', 'integer', 'exists:travel_styles,id'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'currency' => ['nullable', 'regex:/^[A-Z]{3}$/'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'integer', 'min:1', 'max:99'],
            'discount_starts_at' => ['nullable', 'date'],
            'discount_ends_at' => ['nullable', 'date', 'after_or_equal:discount_starts_at'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'duration_nights' => ['nullable', 'integer', 'min:0', 'max:364'],
            'transport_ids' => ['nullable', 'array'],
            'transport_ids.*' => ['integer', 'exists:transport_options,id'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'departures' => ['nullable', 'array'],
            'departures.*.departure_date' => ['nullable', 'date'],
            'departures.*.return_date' => ['nullable', 'date'],
            'departures.*.is_published' => ['nullable', 'boolean'],
            'stays' => ['nullable', 'array'],
            'stays.*.hotel_id' => ['nullable', 'integer', 'exists:hotels,id'],
            'stays.*.translations.*.arrangement' => ['nullable', 'string', 'max:2000'],
            'itinerary' => ['nullable', 'array'],
            'itinerary.*.day_number' => ['nullable', 'integer', 'min:1', 'max:365'],
            'itinerary.*.translations.*.title' => ['nullable', 'string', 'max:180'],
            'itinerary.*.translations.*.body' => $text,
            'points' => ['nullable', 'array'],
            'points.*.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'points.*.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'points.*.day_number' => ['nullable', 'integer', 'min:1', 'max:365'],
            'points.*.translations.*.name' => ['nullable', 'string', 'max:180'],
            'points.*.translations.*.description' => ['nullable', 'string', 'max:2000'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.is_published' => ['nullable', 'boolean'],
            'faqs.*.translations.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.translations.*.answer' => $text,
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'cover_image' => ['nullable'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:500'],
            'translations.*.description' => $text,
            'translations.*.departure_location' => ['nullable', 'string', 'max:180'],
            'translations.*.meeting_instructions' => $text,
            'translations.*.transport_details' => $text,
            'translations.*.meals' => $text,
            'translations.*.notes' => $text,
            'translations.*.requirements' => $text,
            'translations.*.restrictions' => $text,
            'translations.*.terms' => $text,
            'translations.*.cancellation' => $text,
            'translations.*.meta_title' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $days = $this->input('duration_days');
            $nights = $this->input('duration_nights');
            if (filled($days) && filled($nights) && (int) $nights !== (int) $days - 1) {
                $validator->errors()->add('duration_nights', __('admin.validation.duration'));
            }

            if (filled($this->input('discount_price')) && filled($this->input('discount_percent'))) {
                $validator->errors()->add('discount_percent', __('admin.validation.discount_exclusive'));
            }

            if ((filled($this->input('base_price')) || $this->boolean('is_published')) && filled($this->input('base_price')) && ! filled($this->input('currency'))) {
                $validator->errors()->add('currency', __('admin.validation.currency'));
            }

            if ((filled($this->input('discount_price')) || filled($this->input('discount_percent'))) && ! filled($this->input('base_price'))) {
                $validator->errors()->add('base_price', __('admin.validation.price'));
            }

            if (filled($this->input('discount_price')) && filled($this->input('base_price')) && TourPrice::cents($this->input('discount_price')) >= TourPrice::cents($this->input('base_price'))) {
                $validator->errors()->add('discount_price', __('admin.validation.discount_lower'));
            }

            $hasTitle = false;
            foreach ((array) $this->input('translations', []) as $row) {
                if (is_array($row) && filled($row['title'] ?? null)) {
                    $hasTitle = true;
                }
            }
            if ($this->boolean('is_published') && ! $hasTitle) {
                $validator->errors()->add('is_published', __('admin.validation.publish'));
            }

            foreach ((array) $this->input('departures', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $start = $row['departure_date'] ?? null;
                $end = $row['return_date'] ?? null;
                if (! filled($start) && ! filled($end)) {
                    continue;
                }
                if (! filled($start) || ! filled($end)) {
                    $validator->errors()->add('departures.'.$index.'.return_date', __('admin.validation.dates'));

                    continue;
                }
                $from = Carbon::parse((string) $start)->startOfDay();
                $to = Carbon::parse((string) $end)->startOfDay();
                if ($to->lt($from)) {
                    $validator->errors()->add('departures.'.$index.'.return_date', __('admin.validation.dates'));

                    continue;
                }
                $diff = (int) $from->diffInDays($to, true);
                if (filled($days) && (int) $days !== $diff + 1) {
                    $validator->errors()->add('duration_days', __('admin.validation.duration_dates'));
                }
                if (filled($nights) && (int) $nights !== $diff) {
                    $validator->errors()->add('duration_nights', __('admin.validation.duration_dates'));
                }
            }

            $seen = [];
            foreach ((array) $this->input('itinerary', []) as $index => $row) {
                if (! is_array($row) || ! filled($row['day_number'] ?? null)) {
                    continue;
                }
                $number = (int) $row['day_number'];
                if (isset($seen[$number])) {
                    $validator->errors()->add('itinerary.'.$index.'.day_number', __('admin.validation.day'));
                }
                $seen[$number] = true;
            }

            foreach ((array) $this->input('points', []) as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }
                $lat = filled($row['latitude'] ?? null);
                $lng = filled($row['longitude'] ?? null);
                if ($lat !== $lng) {
                    $validator->errors()->add('points.'.$index.'.latitude', __('admin.validation.coordinates'));
                }
            }
        });
    }
}
