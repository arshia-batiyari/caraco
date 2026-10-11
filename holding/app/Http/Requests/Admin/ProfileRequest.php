<?php

namespace App\Http\Requests\Admin;

class ProfileRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_name' => ['nullable', 'string', 'max:120'],
            'translations' => ['nullable', 'array'],
            'translations.*.summary' => ['nullable', 'string', 'max:600'],
            'og_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_og' => ['nullable', 'boolean'],
        ];
    }
}
