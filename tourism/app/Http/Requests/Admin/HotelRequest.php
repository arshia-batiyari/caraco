<?php

namespace App\Http\Requests\Admin;

class HotelRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $this->dropEmptyUploads('images');

        $merge = [];
        foreach (['destination_id', 'latitude', 'longitude', 'website', 'accommodation_category'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }
        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'accommodation_category' => ['nullable', 'string', 'max:120'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'website' => $this->httpRule(),
            'images' => ['nullable', 'array'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'facilities' => ['nullable', 'array'],
            'facilities.*.translations.*.name' => ['nullable', 'string', 'max:180'],
            'translations.*.name' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:500'],
            'translations.*.description' => ['nullable', 'string', 'max:5000'],
            'translations.*.address' => ['nullable', 'string', 'max:500'],
            'translations.*.room_details' => ['nullable', 'string', 'max:5000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }
}
