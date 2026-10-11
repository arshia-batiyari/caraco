<?php

namespace App\Support;

use App\Models\Survey;
use App\Models\SurveyOption;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SurveyRecorder
{
    public function save(Request $request, ?Survey $survey): Survey
    {
        return DB::transaction(function () use ($request, $survey) {
            $payload = [
                'is_active' => $request->boolean('is_active'),
                'is_anonymous' => $request->boolean('is_anonymous'),
                'opens_at' => $request->input('opens_at') ?: null,
                'closes_at' => $request->input('closes_at') ?: null,
                'position' => $request->integer('position'),
            ];
            if ($survey && $survey->responses()->exists()) {
                unset($payload['is_anonymous']);
            }

            if ($survey) {
                $survey->update($payload);
            } else {
                $survey = Survey::query()->create($payload);
            }

            LocaleText::sync($survey, $request->input('translations', []), ['title', 'description'], 'title');

            if (! $survey->responses()->exists()) {
                $this->syncQuestions($survey, $request);
            }

            return $survey->refresh();
        });
    }

    private function syncQuestions(Survey $survey, Request $request): void
    {
        $keptQuestions = [];
        $position = 0;
        foreach ($request->input('questions', []) as $row) {
            if (! is_array($row) || ! $this->filled($row['translations'] ?? [], 'prompt')) {
                continue;
            }
            $question = null;
            if (filled($row['id'] ?? null)) {
                $question = $survey->questions()->whereKey($row['id'])->first();
            }
            $attributes = [
                'type' => $row['type'],
                'is_required' => filter_var($row['is_required'] ?? false, FILTER_VALIDATE_BOOL),
                'position' => $position,
            ];
            if ($question) {
                $question->update($attributes);
            } else {
                $question = $survey->questions()->create($attributes);
            }
            $position++;
            $keptQuestions[] = $question->id;
            LocaleText::sync($question, is_array($row['translations'] ?? null) ? $row['translations'] : [], ['prompt'], 'prompt');
            if (in_array($question->type, ['single', 'multiple'], true)) {
                $this->syncOptions($question, $row['options'] ?? []);
            } else {
                $question->options()->delete();
            }
        }

        $survey->questions()->whereNotIn('id', $keptQuestions)->delete();
    }

    /**
     * @param  array<int, mixed>  $options
     */
    private function syncOptions(SurveyQuestion $question, array $options): void
    {
        $kept = [];
        $position = 0;
        foreach ($options as $row) {
            if (! is_array($row) || ! $this->filled($row['translations'] ?? [], 'label')) {
                continue;
            }
            $option = null;
            if (filled($row['id'] ?? null)) {
                $option = $question->options()->whereKey($row['id'])->first();
            }
            if ($option) {
                $option->update(['position' => $position]);
            } else {
                $option = $question->options()->create(['position' => $position]);
            }
            $position++;
            $kept[] = $option->id;
            LocaleText::sync($option, is_array($row['translations'] ?? null) ? $row['translations'] : [], ['label'], 'label');
        }

        $question->options()->whereNotIn('id', $kept)->delete();
    }

    /**
     * @param  array<string, mixed>  $translations
     */
    private function filled(array $translations, string $column): bool
    {
        foreach ($translations as $row) {
            if (is_array($row) && filled($row[$column] ?? null)) {
                return true;
            }
        }

        return false;
    }
}
