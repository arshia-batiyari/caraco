<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;

class MedicalDoctorRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('medical_provider_id') === '') {
            $this->merge(['medical_provider_id' => null]);
        }
        $this->dropEmptyUploads('image');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'medical_provider_id' => ['nullable', 'integer', 'exists:medical_providers,id'],
            'is_published' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'remove_image' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'translations.*.name' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.specialty' => ['nullable', 'string', 'max:180'],
            'translations.*.biography' => ['nullable', 'string', 'max:20000'],
            'translations.*.image_alt' => ['nullable', 'string', 'max:180'],
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
