<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'message' => ['required', 'string', 'max:2000'],
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
            'name.max' => __('ui.validation.max', ['field' => __('ui.fields.name')]),
            'email.required' => __('ui.validation.required', ['field' => __('ui.fields.email')]),
            'email.email' => __('ui.validation.email'),
            'email.max' => __('ui.validation.max', ['field' => __('ui.fields.email')]),
            'message.required' => __('ui.validation.required', ['field' => __('ui.fields.message')]),
            'message.max' => __('ui.validation.max', ['field' => __('ui.fields.message')]),
        ];
    }

    public function honeypotFilled(): bool
    {
        return filled($this->input('company_website'));
    }
}
