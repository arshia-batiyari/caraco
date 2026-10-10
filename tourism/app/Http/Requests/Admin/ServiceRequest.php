<?php

namespace App\Http\Requests\Admin;

class ServiceRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:included,excluded'],
            'is_published' => ['nullable', 'boolean'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'translations.*.name' => ['nullable', 'string', 'max:180'],
            'translations.*.description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
