<?php

namespace App\Http\Requests\Admin;

class MedicalPageRequest extends AdminRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'translations.*.title' => ['nullable', 'string', 'max:180'],
            'translations.*.summary' => ['nullable', 'string', 'max:2000'],
            'translations.*.body' => ['nullable', 'string', 'max:20000'],
            'translations.*.travel_assistance' => ['nullable', 'string', 'max:8000'],
            'translations.*.stay_assistance' => ['nullable', 'string', 'max:8000'],
            'translations.*.contact_note' => ['nullable', 'string', 'max:4000'],
            'translations.*.meta_title' => ['nullable', 'string', 'max:180'],
            'translations.*.meta_description' => ['nullable', 'string', 'max:320'],
            'translations.*.canonical_url' => $this->canonicalRule(),
        ];
    }
}
