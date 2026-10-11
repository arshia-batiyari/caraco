<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;

class MedicalCategoryRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_published' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'translations.*.name' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:1000'],
            'translations.*.description' => ['nullable', 'string', 'max:20000'],
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
