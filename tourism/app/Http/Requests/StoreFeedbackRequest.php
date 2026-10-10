<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['name', 'email', 'phone', 'tour_id', 'service_id', 'rating_overall', 'rating_accommodation', 'rating_transport', 'rating_organization', 'rating_service'] as $field) {
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
        $rating = ['nullable', 'integer', 'min:1', 'max:5'];

        return [
            'type' => ['required', 'in:general,complaint,suggestion,compliment'],
            'is_anonymous' => ['nullable', 'boolean'],
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'wants_follow_up' => ['nullable', 'boolean'],
            'tour_id' => ['nullable', 'integer', Rule::exists('tours', 'id')->where('is_published', true)],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('is_published', true)],
            'rating_overall' => $rating,
            'rating_accommodation' => $rating,
            'rating_transport' => $rating,
            'rating_organization' => $rating,
            'rating_service' => $rating,
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
            'preferred_language' => ['required', 'in:'.$locales],
            'consent' => ['accepted'],
            'company_website' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $anonymous = $this->boolean('is_anonymous');
            $publicType = $this->input('type') !== 'complaint';
            if ($publicType && ! $anonymous && ! filled($this->input('name'))) {
                $validator->errors()->add('name', __('ui.feedback.validation.name'));
            }

            if ($this->boolean('wants_follow_up') && ! filled($this->input('email')) && ! filled($this->input('phone'))) {
                $validator->errors()->add('email', __('ui.feedback.validation.contact'));
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => __('ui.validation.required', ['field' => __('ui.feedback.type')]),
            'title.required' => __('ui.validation.required', ['field' => __('ui.feedback.subject')]),
            'message.required' => __('ui.validation.required', ['field' => __('ui.fields.message')]),
            'consent.accepted' => __('ui.validation.consent'),
            'preferred_language.required' => __('ui.validation.required', ['field' => __('ui.fields.language')]),
            'email.email' => __('ui.validation.email'),
        ];
    }

    public function honeypotFilled(): bool
    {
        return filled($this->input('company_website'));
    }
}
