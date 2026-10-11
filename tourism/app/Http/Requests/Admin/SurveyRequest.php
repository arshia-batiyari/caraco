<?php

namespace App\Http\Requests\Admin;

use App\Models\Survey;
use Illuminate\Contracts\Validation\Validator;

class SurveyRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['opens_at', 'closes_at'] as $field) {
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
        $rules = [
            'is_active' => ['nullable', 'boolean'],
            'is_anonymous' => ['nullable', 'boolean'],
            'opens_at' => ['nullable', 'date'],
            'closes_at' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'translations' => ['nullable', 'array'],
            'translations.*.title' => ['nullable', 'string', 'max:180'],
            'translations.*.description' => ['nullable', 'string', 'max:5000'],
        ];

        if ($this->structureLocked()) {
            return $rules;
        }

        return $rules + [
            'questions' => ['nullable', 'array'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.type' => ['nullable', 'in:single,multiple,rating,text'],
            'questions.*.is_required' => ['nullable', 'boolean'],
            'questions.*.translations.*.prompt' => ['nullable', 'string', 'max:255'],
            'questions.*.options' => ['nullable', 'array'],
            'questions.*.options.*.id' => ['nullable', 'integer'],
            'questions.*.options.*.translations.*.label' => ['nullable', 'string', 'max:180'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (filled($this->input('opens_at')) && filled($this->input('closes_at')) && $this->input('opens_at') > $this->input('closes_at')) {
                $validator->errors()->add('closes_at', __('admin.validation.survey_dates'));
            }

            $hasTitle = false;
            foreach ((array) $this->input('translations', []) as $row) {
                if (is_array($row) && filled($row['title'] ?? null)) {
                    $hasTitle = true;
                }
            }
            if ($this->boolean('is_active') && ! $hasTitle) {
                $validator->errors()->add('is_active', __('admin.validation.survey_title'));
            }

            if ($this->structureLocked()) {
                return;
            }

            $questions = 0;
            foreach ((array) $this->input('questions', []) as $index => $question) {
                if (! is_array($question) || ! $this->questionFilled($question)) {
                    continue;
                }
                $questions++;
                if (! filled($question['type'] ?? null)) {
                    $validator->errors()->add('questions.'.$index.'.type', __('admin.validation.survey_question'));
                }
                if (in_array($question['type'] ?? '', ['single', 'multiple'], true) && ! $this->optionFilled($question)) {
                    $validator->errors()->add('questions.'.$index.'.type', __('admin.validation.survey_options'));
                }
            }
            if ($this->boolean('is_active') && $questions === 0) {
                $validator->errors()->add('is_active', __('admin.validation.survey_question'));
            }
        });
    }

    public function structureLocked(): bool
    {
        $survey = $this->route('survey');

        return $survey instanceof Survey && $survey->responses()->exists();
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function questionFilled(array $question): bool
    {
        foreach ((array) ($question['translations'] ?? []) as $row) {
            if (is_array($row) && filled($row['prompt'] ?? null)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function optionFilled(array $question): bool
    {
        foreach ((array) ($question['options'] ?? []) as $option) {
            if (! is_array($option)) {
                continue;
            }
            foreach ((array) ($option['translations'] ?? []) as $row) {
                if (is_array($row) && filled($row['label'] ?? null)) {
                    return true;
                }
            }
        }

        return false;
    }
}
