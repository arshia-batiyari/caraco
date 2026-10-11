<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;

class MedicalProviderRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->input('destination_id') === '') {
            $this->merge(['destination_id' => null]);
        }
        $this->dropEmptyUploads('cover');
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'is_published' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:medical_services,id'],
            'remove_cover' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
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
