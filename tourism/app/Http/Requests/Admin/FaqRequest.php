<?php

namespace App\Http\Requests\Admin;

class FaqRequest extends AdminRequest
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
            'translations.*.answer' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
