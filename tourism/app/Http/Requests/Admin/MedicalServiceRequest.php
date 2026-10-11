<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;

class MedicalServiceRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('medical_category_id') === '') {
            $this->merge(['medical_category_id' => null]);
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
            'medical_category_id' => ['nullable', 'integer', 'exists:medical_categories,id'],
            'is_published' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'provider_ids' => ['nullable', 'array'],
            'provider_ids.*' => ['integer', 'exists:medical_providers,id'],
            'remove_cover' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
            'images_meta' => ['nullable', 'array'],
            'images_meta.*.translations.*.alt' => ['nullable', 'string', 'max:180'],
            'images_meta.*.translations.*.caption' => ['nullable', 'string', 'max:255'],
            'translations.*.name' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:1000'],
            'translations.*.description' => ['nullable', 'string', 'max:20000'],
            'translations.*.cover_alt' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->requirePublishedText($validator, 'name');
    }
}
