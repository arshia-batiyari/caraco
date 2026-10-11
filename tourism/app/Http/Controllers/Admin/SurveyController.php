<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SurveyRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Support\SurveyRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SurveyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Survey::class);

        return view('admin.surveys.index', [
            'records' => \App\Support\AdminList::paginate(Survey::query()->with('translations')->withCount('responses'), request(), 'title'),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Survey::class);

        return view('admin.surveys.form', [
            'record' => new Survey(['is_anonymous' => true, 'position' => 0]),
            'locked' => false,
        ]);
    }

    public function store(SurveyRequest $request, SurveyRecorder $recorder): RedirectResponse
    {
        $this->authorize('create', Survey::class);
        $survey = $recorder->save($request, null);

        return redirect()->route('admin.surveys.edit', $survey)->with('status', __('admin.saved'));
    }

    public function edit(Survey $survey): View
    {
        $this->authorize('update', $survey);

        return view('admin.surveys.form', [
            'record' => $survey->load(['translations', 'questions.translations', 'questions.options.translations']),
            'locked' => $survey->responses()->exists(),
        ]);
    }

    public function update(SurveyRequest $request, Survey $survey, SurveyRecorder $recorder): RedirectResponse
    {
        $this->authorize('update', $survey);
        $recorder->save($request, $survey);

        return redirect()->route('admin.surveys.edit', $survey)->with('status', __('admin.saved'));
    }

    public function destroy(Survey $survey): RedirectResponse
    {
        $this->authorize('delete', $survey);
        $this->requireDeleteConfirmation();
        $survey->delete();

        return redirect()->route('admin.surveys.index')->with('status', __('admin.saved'));
    }

    public function results(Survey $survey): View
    {
        $this->authorize('view', $survey);
        $survey->load(['translations', 'questions.translations', 'questions.options.translations', 'questions.answers']);

        return view('admin.surveys.results', [
            'record' => $survey,
            'summary' => $this->summary($survey),
        ]);
    }

    public function export(Survey $survey): StreamedResponse
    {
        $this->authorize('view', $survey);
        $survey->load(['questions.translations', 'questions.options.translations', 'responses.answers']);

        return response()->streamDownload(function () use ($survey) {
            $handle = fopen('php://output', 'w');
            $header = ['locale', 'submitted_at'];
            if (! $survey->is_anonymous) {
                $header[] = 'name';
                $header[] = 'email';
            }
            foreach ($survey->questions as $question) {
                $header[] = $question->publicTranslation()?->prompt ?? ('#'.$question->id);
            }
            fputcsv($handle, $header);

            foreach ($survey->responses as $response) {
                $row = [$response->locale, $response->created_at?->toDateTimeString()];
                if (! $survey->is_anonymous) {
                    $row[] = $response->name;
                    $row[] = $response->email;
                }
                foreach ($survey->questions as $question) {
                    $row[] = $this->answerText($question, $response->answers->where('survey_question_id', $question->id));
                }
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'survey-'.$survey->id.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return list<array{question: SurveyQuestion, kind: string, rows: list<array{label: string, count: int}>, average: ?float, texts: list<string>}>
     */
    private function summary(Survey $survey): array
    {
        $blocks = [];
        foreach ($survey->questions as $question) {
            $answers = $question->answers;
            if ($question->type === 'rating') {
                $counts = [];
                for ($score = 1; $score <= 5; $score++) {
                    $counts[] = ['label' => (string) $score, 'count' => $answers->where('rating_value', $score)->count()];
                }
                $rated = $answers->whereNotNull('rating_value');
                $blocks[] = [
                    'question' => $question,
                    'kind' => 'rating',
                    'rows' => $counts,
                    'average' => $rated->isEmpty() ? null : round($rated->avg('rating_value'), 2),
                    'texts' => [],
                ];
            } elseif (in_array($question->type, ['single', 'multiple'], true)) {
                $rows = [];
                foreach ($question->options as $option) {
                    $rows[] = [
                        'label' => $option->publicTranslation()?->label ?? ('#'.$option->id),
                        'count' => $answers->where('survey_option_id', $option->id)->count(),
                    ];
                }
                $blocks[] = [
                    'question' => $question,
                    'kind' => 'choice',
                    'rows' => $rows,
                    'average' => null,
                    'texts' => [],
                ];
            } else {
                $blocks[] = [
                    'question' => $question,
                    'kind' => 'text',
                    'rows' => [],
                    'average' => null,
                    'texts' => $answers->pluck('text_value')->filter()->values()->all(),
                ];
            }
        }

        return $blocks;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\SurveyAnswer>  $answers
     */
    private function answerText(SurveyQuestion $question, $answers): string
    {
        if ($question->type === 'rating') {
            return (string) ($answers->first()?->rating_value ?? '');
        }
        if ($question->type === 'text') {
            return (string) ($answers->first()?->text_value ?? '');
        }

        return $answers->map(function ($answer) use ($question) {
            $option = $question->options->firstWhere('id', $answer->survey_option_id);

            return $option?->publicTranslation()?->label ?? '';
        })->filter()->implode('; ');
    }
}
