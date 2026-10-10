<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
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
            'tour_id' => ['nullable', 'integer', 'exists:tours,id'],
            'destination_id' => ['nullable', 'integer', 'exists:destinations,id'],
            'destination_text' => ['nullable', 'string', 'max:180'],
            'travel_start' => ['nullable', 'date'],
            'travel_end' => ['nullable', 'date', 'after_or_equal:travel_start'],
            'travelers' => ['nullable', 'integer', 'min:1', 'max:99'],
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
            'message.required' => __('ui.validation.required', ['field' => __('ui.fields.message')]),
            'consent.accepted' => __('ui.validation.consent'),
            'preferred_language.required' => __('ui.validation.required', ['field' => __('ui.fields.language')]),
        ];
    }

    public function honeypotFilled(): bool
    {
        return filled($this->input('company_website'));
    }
}
