<?php

namespace App\Http\Requests\Admin;

class ProjectRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'position' => ['required', 'integer', 'min:0', 'max:999'],
            'is_published' => ['nullable', 'boolean'],
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:180'],
            'translations.*.slug' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:600'],
            'translations.*.body' => ['nullable', 'string', 'max:20000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->hasTitle()) {
                $validator->errors()->add('translations.en.title', __('admin.validation.title'));
            }
        });
    }

    public function hasTitle(): bool
    {
        $rows = $this->input('translations', []);
        if (! is_array($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if (is_array($row) && filled(trim((string) ($row['title'] ?? '')))) {
                return true;
            }
        }

        return false;
    }
}
