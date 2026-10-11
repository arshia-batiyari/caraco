<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMedicalInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['phone', 'country', 'medical_service_id'] as $field) {
            if ($this->input($field) === '') {
                $merge[$field] = null;
            }
        }
        $this->merge($merge);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $locales = implode(',', array_keys(config('karako.locales')));

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'preferred_language' => ['required', 'in:'.$locales],
            'country' => ['nullable', 'string', 'max:80'],
            'medical_service_id' => ['nullable', 'integer', Rule::exists('medical_services', 'id')->where('is_published', true)],
            'message' => ['required', 'string', 'max:4000'],
            'consent' => ['accepted'],
            'company_website' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('ui.validation.required', ['field' => __('ui.fields.name')]),
            'email.required' => __('ui.validation.required', ['field' => __('ui.fields.email')]),
            'email.email' => __('ui.validation.email'),
            'message.required' => __('ui.validation.required', ['field' => __('ui.medical.questions')]),
            'consent.accepted' => __('ui.validation.consent'),
            'preferred_language.required' => __('ui.validation.required', ['field' => __('ui.fields.language')]),
        ];
    }

    public function honeypotFilled(): bool
    {
        return filled($this->input('company_website'));
    }
}
