<?php

namespace App\Http\Requests;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSurveyResponseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['name', 'email'] as $field) {
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
        return [
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'answers' => ['nullable', 'array'],
            'company_website' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $survey = $this->route('survey');
            if (! $survey instanceof Survey) {
                $survey = Survey::query()->find($survey);
            }
            if (! $survey instanceof Survey || $validator->errors()->isNotEmpty()) {
                return;
            }

            $survey->load('questions.options');
            foreach ($survey->questions as $question) {
                $this->checkQuestion($validator, $question);
            }
        });
    }

    public function honeypotFilled(): bool
    {
        return filled($this->input('company_website'));
    }

    private function checkQuestion(Validator $validator, SurveyQuestion $question): void
    {
        $value = $this->input('answers.'.$question->id);
        $key = 'answers.'.$question->id;
        $empty = $value === null || $value === '' || $value === [];

        if ($question->is_required && $empty) {
            $validator->errors()->add($key, __('ui.survey.validation.required'));

            return;
        }
        if ($empty) {
            return;
        }

        $optionIds = $question->options->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($question->type === 'single') {
            if (! in_array((int) $value, $optionIds, true)) {
                $validator->errors()->add($key, __('ui.survey.validation.option'));
            }
        }
        if ($question->type === 'multiple') {
            $choices = is_array($value) ? $value : [$value];
            if ($choices === []) {
                $validator->errors()->add($key, __('ui.survey.validation.option'));
            }
            foreach ($choices as $choice) {
                if (! in_array((int) $choice, $optionIds, true)) {
                    $validator->errors()->add($key, __('ui.survey.validation.option'));
                }
            }
        }
        if ($question->type === 'rating' && (! is_numeric($value) || (int) $value < 1 || (int) $value > 5)) {
            $validator->errors()->add($key, __('ui.survey.validation.rating'));
        }
        if ($question->type === 'text' && mb_strlen((string) $value) > 2000) {
            $validator->errors()->add($key, __('ui.survey.validation.text'));
        }
    }
}
