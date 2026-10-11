<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\Validator;

class MedicalFaqRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_published' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'translations.*.question' => ['nullable', 'string', 'max:255'],
            'translations.*.answer' => ['nullable', 'string', 'max:8000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $this->requirePublishedText($validator, 'question');
    }
}
