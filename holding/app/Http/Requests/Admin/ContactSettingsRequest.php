<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class ContactSettingsRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contact_email' => ['nullable', 'email', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'translations' => ['nullable', 'array'],
            'translations.*.address' => ['nullable', 'string', 'max:500'],
            'translations.*.hours' => ['nullable', 'string', 'max:180'],
            'translations.*.note' => ['nullable', 'string', 'max:1000'],
            'social' => ['nullable', 'array'],
            'social.*.platform' => ['nullable', Rule::in(['instagram', 'linkedin', 'telegram', 'youtube', 'x', 'website'])],
            'social.*.url' => ['nullable', 'string', 'max:255'],
            'social.*.position' => ['nullable', 'integer', 'min:0', 'max:999'],
            'social.*.is_published' => ['nullable', 'boolean'],
            'remove_social' => ['nullable', 'array'],
            'remove_social.*' => ['integer'],
            'new_social' => ['nullable', 'array'],
            'new_social.platform' => ['nullable', Rule::in(['instagram', 'linkedin', 'telegram', 'youtube', 'x', 'website'])],
            'new_social.url' => ['nullable', 'string', 'max:255'],
            'new_social.position' => ['nullable', 'integer', 'min:0', 'max:999'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $rows = $this->input('social', []);
            if (is_array($rows)) {
                foreach ($rows as $index => $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $this->checkSocial($validator, 'social.'.$index, $row);
                }
            }
            $new = $this->input('new_social', []);
            if (is_array($new) && (filled($new['platform'] ?? null) || filled($new['url'] ?? null))) {
                $this->checkSocial($validator, 'new_social', $new, true);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function checkSocial($validator, string $prefix, array $row, bool $required = false): void
    {
        if ($required && ! filled($row['platform'] ?? null)) {
            $validator->errors()->add($prefix.'.platform', __('admin.validation.required'));
        }
        $url = isset($row['url']) ? (string) $row['url'] : '';
        if (($required || filled($row['platform'] ?? null)) && ! \App\Support\HttpUrl::valid($url)) {
            $validator->errors()->add($prefix.'.url', __('admin.validation.url'));
        }
    }
}
