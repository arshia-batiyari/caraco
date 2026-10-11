<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;

class AttractionRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['destination_id', 'attraction_category_id', 'classification', 'inscribed_on', 'unesco_reference', 'latitude', 'longitude'] as $field) {
            if ($this->input($field) === '') {
                $this->merge([$field => null]);
            }
        }
        $this->dropEmptyUploads('cover');
        $this->dropEmptyUploads('images');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'attraction_category_id' => ['nullable', 'integer', 'exists:attraction_categories,id'],
            'classification' => ['nullable', 'in:cultural,natural,mixed'],
            'unesco_designation' => ['nullable', 'in:none,world_heritage'],
            'unesco_verified' => ['nullable', 'boolean'],
            'inscribed_on' => ['nullable', 'date'],
            'unesco_reference' => ['nullable', 'string', 'max:40'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_published' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'tour_ids' => ['nullable', 'array'],
            'tour_ids.*' => ['integer', 'exists:tours,id'],
            'remove_cover' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'images_meta' => ['nullable', 'array'],
            'images_meta.*.translations.*.alt' => ['nullable', 'string', 'max:180'],
            'images_meta.*.translations.*.caption' => ['nullable', 'string', 'max:255'],
            'translations.*.title' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:1000'],
            'translations.*.description' => ['nullable', 'string', 'max:20000'],
            'translations.*.visitor_info' => ['nullable', 'string', 'max:8000'],
            'translations.*.cover_alt' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->requirePublishedText($validator, 'title');
        $this->requireCoordinatePair($validator);
    }
}
